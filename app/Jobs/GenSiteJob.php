<?php

namespace App\Jobs;

use App\Models\Config;
use App\Models\Site;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenSiteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $domain;
    private $origin_site;
    private $title;
    private $keywords;
    private $description;
    private $search;
    private $replace;


    public function __construct($domain, $origin_site, $title, $keywords, $description, $search, $replace)
    {
        $this->domain = $domain;
        $this->origin_site = $origin_site;
        $this->title = $title;
        $this->keywords = $keywords;
        $this->description = $description;
        $this->search = $search;
        $this->replace = $replace;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $domain = $this->domain;
        $top_domain = implode(".", array_slice(explode('.', $domain), 1));
        $origin_site = $this->origin_site;
        $title = $this->title;
        $keywords = $this->keywords;
        $description = $this->description;
        $search = $this->search;
        $replace = $this->replace;
        if (!Site::query()->where('domain', '=', $domain)->exists()) {
            Site::query()->insert(['domain' => $domain, 'origin_site' => $origin_site,
                'title' => $title,
                'keywords' => $keywords,
                'description' => $description,
                'search' => $search,
                'replace' => $replace],
            );
        }
        $search_arr = explode(';', $search);
        $replace_arr = explode(';', $replace);
        $origin_path = storage_path('app/public/www.jingxiang.com');
        $configs = Config::query()->get()->toArray();
        $pathConfigs = array_column($configs, 'value', 'name');
        $site_path = $pathConfigs['site_path'] ?? '';
        $dist_path = $site_path ? rtrim($site_path, "/") . '/' . $domain : storage_path("app/public/{$domain}");
        if (is_dir($dist_path)) {
            $this->makeConf($origin_site, $title, $keywords, $description, $search_arr, $replace_arr, $dist_path);
            return;
        }
        $this->dir_copy($origin_path, $dist_path);
        $this->makeConf($origin_site, $title, $keywords, $description, $search_arr, $replace_arr, $dist_path);
        $nginx_conf_path = $pathConfigs['nginx_conf_path'] ?? '';
        $nginx_conf_path = $nginx_conf_path ?: storage_path("app/public/{$domain}");
        $this->makeNginxConf($domain, $top_domain, $nginx_conf_path, $dist_path);
    }

    private function dir_copy($src = '', $dst = '')
    {
        if (empty($src) || empty($dst)) {
            return false;
        }

        $dir = opendir($src);
        $this->dir_mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->dir_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);

        return true;
    }

    private function makeConf($origin_site, $title, $keywords, $description, $search_arr, $replace_arr, $dist_path)
    {
        $conf_template = $this->configTemplate();
        $params = [$origin_site, $title, $keywords, $description];

        foreach ($search_arr as $k => $item) {
            $params[] = $item;
            $params[] = $replace_arr[$k];
        }
        if (count($params) < 44) {
            $addtional_params = 44 - count($params);
            for ($i = 0; $i < $addtional_params; $i++) {
                $params[] = '';
            }
        } else if (count($params) > 44) {
            $params = array_slice($params, 0, 44);
        }
        $conf_template = sprintf($conf_template, ...$params);
        file_put_contents($dist_path . '/config.php', $conf_template);

    }

    public function makeNginxConf($domain, $top_domain, $nginx_conf_path, $dist_path)
    {
        $nginx_template = $this->nginxTemplate();
        $nginx_root = $dist_path;
        $nginx_template = sprintf($nginx_template, $domain, $top_domain, $nginx_root, $domain, $domain);

        file_put_contents($nginx_conf_path . "/{$domain}.conf", $nginx_template);

    }

    private function dir_mkdir($path = '', $mode = 0777, $recursive = true)
    {
        clearstatcache();
        if (!is_dir($path)) {
            mkdir($path, $mode, $recursive);
            return chmod($path, $mode);
        }

        return true;
    }


    private function configTemplate()
    {
        return <<<'CONF'
<?php
!defined('l_ok') && die('Accessed Defined!');
$gourl='%s';
$mytitle='%s';
$mykeywords='%s';
$mydescription='%s';
$hckg='1';
$hcsj='';
$index_cache_time='';
$mbbianma='UTF-8';
$webtj='';
$jiancekey='';
$tiaozhuan='';
$link='';
$wzdkey='';
$wyckg='';
$rewrite='1';
$uzjc='1';
$setcss='1';
$htmlurl='';
$fanti='';
$jianti='';
$mblink='kRGawxX2MMI';
$mylink='';
$uzvip='abcd';
$snopy='';
$proxy='';
$proxyduankou='';
$proxyip='';
$curl='1';
$mbad1='%s';
$myad1='%s';
$mbad2='%s';
$myad2='%s';
$mbad3='%s';
$myad3='%s';
$mbad4='%s';
$myad4='%s';
$mbad5='%s';
$myad5='%s';
$mbad6='%s';
$myad6='%s';
$mbad7='%s';
$myad7='%s';
$mbad8='%s';
$myad8='%s';
$mbad9='%s';
$myad9='%s';
$mbad10='%s';
$myad10='%s';
$mbad11='%s';
$myad11='%s';
$mbad12='%s';
$myad12='%s';
$mbad13='%s';
$myad13='%s';
$mbad14='%s';
$myad14='%s';
$mbad15='%s';
$myad15='%s';
$mbad16='%s';
$myad16='%s';
$mbad17='%s';
$myad17='%s';
$mbad18='%s';
$myad18='%s';
$mbad19='%s';
$myad19='%s';
$mbad20='%s';
$myad20='%s';
$zhengze1='';
$zhengze2='';
$zhengze3='';
$zhengze4='';
$zhengze5='';
$zhengze6='';
$zhengze7='';
$zhengze8='';
$zhengze9='';
$zhengze10='';
$zhengze11='';
$zhengze12='';
$zhengze13='';
$zhengze14='';
$zhengze15='';
$zhengze16='';
$sourcehtml1='';
$replacehtml1='';
$sourcehtml2='';
$replacehtml2='';
$sourcehtml3='';
$replacehtml3='';
$sourcehtml4='';
$replacehtml4='';
$sourcehtml5='';
$replacehtml5='';
CONF;


    }

    private function nginxTemplate()
    {
        return <<<'NIGNX'
server
 {
   listen       80;
   server_name  %s %s;
   index index.php index.html;
   root %s;
	 if ($host != '%s' ) {
	    rewrite  ^/(.*)$  http://%s/$1  permanent;
	 }
	if (!-e $request_filename) {
		rewrite ^/(.*)$ /index.php?rewrite=$1  last;
	}
   location ~ .*\.(php|php5)?$
   {
     fastcgi_pass unix:/tmp/php-cgi.sock;
     fastcgi_index index.php;
     include fastcgi.conf;
   }
	location =/updata.php{
	  fastcgi_pass unix:/tmp/php-cgi.sock;
	  fastcgi_index index.php;
	  include fastcgi.conf;
	}
	location =/news_up.php{
	  fastcgi_pass unix:/tmp/php-cgi.sock;
	  fastcgi_index index.php;
	  include fastcgi.conf;
	}
	location =/news_post{
	  fastcgi_pass unix:/tmp/php-cgi.sock;
	  fastcgi_index index.php;
	  include fastcgi.conf;
	}
   location ~ .*\.(gif|jpg|jpeg|png|bmp|swf)$
   {
     expires      1d;
   }
   location ~ .*\.(js|css)?$
   {
     expires      1h;
   }
   error_page 404 = /404.html;
}


NIGNX;


    }
}
