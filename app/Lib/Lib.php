<?php

namespace App\Lib;

use App\Model\Script;
use App\Model\Skin;
use Auth;
use Illuminate\Support\Facades\Storage;
use Image;

class Lib
{
    /**
     * Usefull functions
     */

    /**
     * Renvoie true si l'user doit être limité
     * @param int $seconds
     * @return boolean limited comment
     */
    public function limitComment($seconds)
    {
        $user = Auth::user();
        if (!$user) {
            return true;
        }
        return $user->comments()->where('created_at', '>', \Carbon\Carbon::now()->subSeconds($seconds))->count();
    }

    public function adminOrFail()
    {
        if (!(Auth::check() && Auth::user()->isAdmin())) {
            abort(404);
        }
    }

    public function ownerOradminOrFail($user_id)
    {
        //si c'est l'owner de l'objet (script/skin) on laisse passer
        if (!(Auth::check() && Auth::user()->id == $user_id)) {
            $this->adminOrFail();
        }
    }

    public function storeImage($item, $file)
    {
        Storage::delete('public/images/' . $item->photoShortLink());
        Storage::delete('public/images/small-' . $item->photoShortLink());
        $filename = $item->slug;
        $filename = strtolower(preg_replace('/[^a-zA-Z0-9-_\.]/', '-', $filename));

        $img = Image::make($file);

        if ($img->mime() != 'image/png') {
            $img->encode('jpg');
            $filename = $filename . ".jpg";
        } else {
            $filename = $filename . ".png";
        }

        //== RESIZE NORMAL ==
        $img->resize(1000, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        $img->resize(null, 1000, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        \File::exists(storage_path('app/public/images/')) or \File::makeDirectory(storage_path('app/public/images/'));
        $img->save(storage_path('app/public/images/') . $filename, 90);

        //== RESIZE MINIATURE ==
        $img->resize(345, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        $img->resize(null, 345, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        $img->save(storage_path('app/public/images/small-') . $filename, 85);

        //store photo in DB
        $item->photo_url = $filename;
        $item->save();
    }

    public function sendDiscord($content, $url)
    {
        $data = ["content" => $content];
        $data_string = json_encode($data);
        $opts = [
            'http' => [
                'method' => "POST",
                "name" => "jvscript.io",
                "user_name" => "jvscript.io",
                'header' => "Content-Type: application/json\r\n",
                'content' => $data_string
            ]
        ];

        try {
            $context = stream_context_create($opts);
            file_get_contents($url, false, $context);
        } catch (\Exception $ex) {
            return;
        }
    }

    public function isImage($path)
    {
        try {
            if (!is_array(getimagesize($path))) {
                return false;
            }

            $a = getimagesize($path);

            $image_type = $a[2];

            if (in_array($image_type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP])) {
                return true;
            }
            return false;
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function crawlInfo()
    {
        set_time_limit(600);
        $scripts = Script::where("status", 1)->orderBy('last_update', 'asc')->get();
        foreach ($scripts as $script) {
            echo "start   : " . $script->name . "\n";
            if (preg_match('/https:\/\/github\.com\/(.*)\/(.*)\/raw\/(.*)\/(.*)\.js/i', $script->js_url, $match) || preg_match('/https:\/\/raw\.githubusercontent\.com\/(.*)\/(.*)\/(.*)\/(.*)\.js/i', $script->js_url, $match)) {
                $url_crawl = "https://github.com/$match[1]/$match[2]/blob/$match[3]/$match[4].js";
                $crawl_content = @file_get_contents($url_crawl);
                if (preg_match('/<relative-time datetime="(.*Z)">/i', $crawl_content, $match_date)) {
                    $date = $match_date[1];
                    $date = \Carbon\Carbon::parse($date);
                    $script->last_update = $date;
                    $script->save();
                    echo $script->js_url . "|$url_crawl|$date\n";
                } else {
                    echo "fail : " . $script->js_url . "|$url_crawl\n";
                }
            } elseif (preg_match('/https:\/\/(.*)\.github\.io\/(.*)\/(.*)\.js/i', $script->js_url, $match)) {
                //GITHUB PAGES
                $url_crawl = "https://github.com/$match[1]/$match[2]/blob/master/$match[3].js";
                $crawl_content = @file_get_contents($url_crawl);
                if (preg_match('/<relative-time datetime="(.*Z)">/i', $crawl_content, $match_date)) {
                    $date = $match_date[1];
                    $date = \Carbon\Carbon::parse($date);
                    $script->last_update = $date;
                    $script->save();
                    echo $script->js_url . "|$url_crawl|$date\n";
                } else {
                    echo "fail : " . $script->js_url . "|$url_crawl\n";
                }
            } elseif (preg_match('/https:\/\/openuserjs\.org\/install\/(.*)\/(.*)\.user\.js/i', $script->js_url, $match) || preg_match('/https:\/\/openuserjs\.org\/src\/scripts\/(.*)\/(.*)\.user\.js/i', $script->js_url, $match)) {
                $url_crawl = "https://openuserjs.org/scripts/$match[1]/$match[2]";
                $crawl_content = @file_get_contents($url_crawl);
                if (preg_match('/<time class="script-updated" datetime="(.*Z)" title=/i', $crawl_content, $match_date)) {
                    $date = $match_date[1];
                    $date = \Carbon\Carbon::parse($date);
                    $script->last_update = $date;
                    $script->save();
                    echo $script->js_url . "|$url_crawl|$date\n";
                } elseif (preg_match('/<b>Published:<\/b> <time datetime="(.*Z)"/i', $crawl_content, $match_date)) {
                    $date = $match_date[1];
                    $date = \Carbon\Carbon::parse($date);
                    $script->last_update = $date;
                    $script->save();
                    echo $script->js_url . "|$url_crawl|$date\n";
                } else {
                    echo "fail : " . $script->js_url . "|$url_crawl\n";
                }
                //get version openuserjs in same page
                if (preg_match('/<code>([0-9.]+).*<\/code>/i', $crawl_content, $match)) {
                    $script->version = strip_tags($match[1]);
                    $script->save();
                    echo $script->js_url . "|$url_crawl|version : $script->version\n";
                }
            } elseif (preg_match('/https:\/\/greasyfork.org\/scripts\/(.*)\/code\/(.*)\.user\.js/i', $script->js_url, $match)) {
                $url_crawl = "https://greasyfork.org/fr/scripts/$match[1]";
                $crawl_content = @file_get_contents($url_crawl);
                if (preg_match('/updated-date"><span><time datetime="(.*)">(.*)<\/time>/i', $crawl_content, $match_date)) {
                    $date = $match_date[1];
                    $date = \Carbon\Carbon::parse($date);
                    $script->last_update = $date;
                    $script->save();
                    echo $script->js_url . "|$url_crawl|$date\n";
                } else {
                    echo "fail : " . $script->js_url . "|$url_crawl\n";
                }
            }

            //===GET  VERSION===
            $url_crawl = $script->js_url;

            if (!str_contains($url_crawl, 'openuserjs')) {
                $crawl_content = @file_get_contents($url_crawl);
                if (preg_match('/\/\/\s*@version\s*(.*)/i', $crawl_content, $match_date)) {
                    $version = $match_date[1];
                    $script->version = $version;
                    $script->save();
                    echo $script->js_url . "|version : $version\n";
                } else {
                    echo "fail version : " . $script->js_url . "\n";
                }
            }
        }

        $scripts = Skin::where("status", 1)->orderBy('last_update', 'asc')->get();
        foreach ($scripts as $script) {
            $url_crawl = $script->skin_url;
            $crawl_content = @file_get_contents($url_crawl);
            if (preg_match('/<th>Updated<\/th>\n\s*<td>(.*)<\/td>/i', $crawl_content, $match_date)) {
                $date = $match_date[1];
                $date = \Carbon\Carbon::parse($date);
                $script->last_update = $date;
                $script->save();
                echo $script->js_url . "|$url_crawl|$date\n";
            } else {
                echo "fail : " . $script->js_url . "|$url_crawl\n";
            }
        }
    }
}
