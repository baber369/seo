<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\ImportSite;
use App\Jobs\UpdateJsJob;
use App\Models\Config;
use App\Models\Site;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class SiteController extends AdminController
{

    public function configPath(Content $content)
    {
        $configs=Config::query()->whereIn('name',['site_path','nginx_conf_path'])->get()->toArray();
        $configs=array_column($configs,'value','name');

        $form=new Form(new Config());
        $form->text("site_path","网站路径")->default($configs['site_path']??'')->required();
        $form->text("nginx_conf_path",'Nginx 配置路径')->default($configs['nginx_conf_path']??'')->required();
        $form->setAction(route("admin.config.store"));
        $form->tools(function (Form\Tools $tools){
            $tools->disableList();
        });
        $form->footer(function (Form\Footer $footer){
            $footer->disableViewCheck();
            $footer->disableCreatingCheck();
            $footer->disableEditingCheck();
        });

        return $content->title("配置路径")->body($form);


    }

    public function storeConfig(Request $request)
    {
        $site_path=$request->post("site_path","");
        $nginx_conf_path=$request->post('nginx_conf_path','');
        if (empty($site_path)||empty($nginx_conf_path)){
            admin_error("参数错误","网站路径和 nginx 配置不能为空");
            return redirect()->route('admin.config.index');
        }
        $site_path=str_replace('\\','/',$site_path);
        $nginx_conf_path=str_replace('\\','/',$nginx_conf_path);
        if (Config::query()->where('name','=','site_path')->exists()){
            Config::query()->where('name','=','site_path')->update(['value'=>$site_path]);
        }else{
            Config::query()->insert(['name'=>'site_path','value'=>$site_path]);
        }
        if (Config::query()->where('name','=','nginx_conf_path')->exists()){
            Config::query()->where('name','=','nginx_conf_path')->update(['value'=>$nginx_conf_path]);
        }else{
            Config::query()->insert(['name'=>'nginx_conf_path','value'=>$nginx_conf_path]);
        }
        admin_success('配置成功');
        return redirect()->route('admin.config.index');

    }


    public function config51LaJs(Content $content)
    {
        $configs=Config::query()->where('name','=','51la_js')->first();

        $form=new Form(new Config());
        $form->textarea("51la_js","51la js内容")->default($configs->value??'')->required()->rows(20);
        $form->setAction(route("admin.config.51la_js.store"));
        $form->tools(function (Form\Tools $tools){
            $tools->disableList();
        });
        $form->footer(function (Form\Footer $footer){
            $footer->disableViewCheck();
            $footer->disableCreatingCheck();
            $footer->disableEditingCheck();
        });

        return $content->title("51la JS 配置")->body($form);

    }

    public function store51LaJs(Request $request)
    {
        $js=$request->post('51la_js');
        if (empty($js)){
            admin_error("参数错误","js不能为空");
            return redirect()->route('admin.config.51la_js');
        }
        if (Config::query()->where('name','=','51la_js')->exists()){
            if(Config::query()->where('name','=','51la_js')->update(['value'=>$js])){
                dispatch(new UpdateJsJob('51la_js'));
                admin_success('配置成功');
                return redirect()->route('admin.config.51la_js');
            }
        }else{
            if(Config::query()->insert(['name'=>'51la_js','value'=>$js])){
                dispatch(new UpdateJsJob('51la_js'));
                admin_success('配置成功');
                return redirect()->route('admin.config.51la_js');
            }
        }


        admin_error("插入数据失败",);
        return redirect()->route('admin.config.51la_js');

    }

    public function configAdvJs(Content $content)
    {
        $configs=Config::query()->where('name','=','adv_js')->first();

        $form=new Form(new Config());
        $form->textarea("adv_js","广告文件内容")->default($configs->value??'')->required()->rows(20);
        $form->setAction(route("admin.config.adv_js.store"));
        $form->tools(function (Form\Tools $tools){
            $tools->disableList();
        });
        $form->footer(function (Form\Footer $footer){
            $footer->disableViewCheck();
            $footer->disableCreatingCheck();
            $footer->disableEditingCheck();
        });

        return $content->title("广告文件配置")->body($form);
    }

    public function storeAdvJs(Request $request)
    {
        $js=$request->post('adv_js');
        if (empty($js)){
            admin_error("参数错误","js不能为空");
            return redirect()->route('admin.config.adv_js');
        }
        if (Config::query()->where('name','=','adv_js')->exists()){
            if(Config::query()->where('name','=','adv_js')->update(['value'=>$js])){
                dispatch(new UpdateJsJob('adv_js'));
                admin_success('配置成功');
                return redirect()->route('admin.config.adv_js');
            }
        }else{
            if(Config::query()->insert(['name'=>'adv_js','value'=>$js])){
                dispatch(new UpdateJsJob('adv_js'));
                admin_success('配置成功');
                return redirect()->route('admin.config.adv_js');
            }
        }


        admin_error("插入数据失败",);
        return redirect()->route('admin.config.adv_js');
    }
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '站点管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Site());
        $grid->column('id', 'ID');
        $grid->column('domain', '域名');
        $grid->column('origin_site', '源站');
        $grid->column('title', '首页标题');
        $grid->column('keywords', '关键词');
        $grid->column('description', '描述');
        $grid->actions(function (Grid\Displayers\Actions $actions){
            $actions->disableView();

        });
        $grid->tools(function (Grid\Tools $tools){
            $tools->append(new ImportSite());
        });
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Site::query()->findOrFail($id));


        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Site());
        if ($form->isCreating()) {
            $form->text('domain', '域名')->required();
        } else {
            $form->text('domain', '域名')->disable();
        }

        $form->text('origin_site', '源站')->required();
        $form->text('title', '首页标题')->required();
        $form->text('keywords', '关键词')->required();
        $form->text('description', '描述')->required();
        $form->text('search', '被替换词')->help("以英文 ; 分割，例如 张三;李四;王五");
        $form->text('replace', '替换词')->help("以英文 ; 分割，例如 张三;李四;王五，和被替换词一一对应");
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
            $tools->disableList();
        });
        $form->footer(function (Form\Footer $footer) {
            $footer->disableReset();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
            $footer->disableViewCheck();
        });

        $form->saved(function (Form $form) {
            $domain = $form->model()->domain;
            $top_domain = implode(".", array_slice(explode('.', $domain), 1));
            $origin_site = $form->model()->origin_site;
            $title = $form->model()->title;
            $keywords = $form->model()->keywords;
            $description = $form->model()->description;
            $search = $form->model()->search;
            $replace = $form->model()->replace;
            $search_arr = explode(';', $search);
            $replace_arr = explode(';', $replace);
            $origin_path = storage_path('app/public/www.jingxiang.com');
            $configs=Config::query()->get()->toArray();
            $pathConfigs=array_column($configs,'value','name');
            $site_path=$pathConfigs['site_path']??'';
            $dist_path=$site_path?rtrim($site_path,"/").'/'.$domain:storage_path("app/public/{$domain}");
            if (is_dir($dist_path)) {
                $this->makeConf($origin_site,$title,$keywords,$description,$search_arr,$replace_arr,$dist_path);
                return;
            }
            $this->dir_copy($origin_path, $dist_path);
            $this->makeConf($origin_site,$title,$keywords,$description,$search_arr,$replace_arr,$dist_path);
            $nginx_conf_path=$pathConfigs['nginx_conf_path']??'';
            $nginx_conf_path=$nginx_conf_path?:storage_path("app/public/{$domain}");
            $this->makeNginxConf($domain,$top_domain,$nginx_conf_path,$dist_path);
        });


        return $form;
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

    public function makeNginxConf($domain,$top_domain,$nginx_conf_path,$dist_path)
    {
        $nginx_template = $this->nginxTemplate();
        $nginx_root = $dist_path;
        $nginx_template = sprintf($nginx_template, $domain, $top_domain, $nginx_root, $domain, $domain);

        file_put_contents($nginx_conf_path. "/{$domain}.conf", $nginx_template);

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
