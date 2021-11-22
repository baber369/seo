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

class UpdateJsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    private $js_name;

    public function __construct($js_name)
    {
        $this->js_name=$js_name;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $jsConfig=Config::query()->where("name","=",$this->js_name)->first();
        if ($jsConfig){
            $sitePathConfig=Config::query()->where("name","=","site_path")->first();
            $sitePath=$sitePathConfig->value;
            $this->updateJs($sitePath,$this->js_name,$jsConfig->value);

        }

    }

    private function updateJs($sitePath,$js_name,$js_content){
        $dir=dir($sitePath);
       while($site=$dir->read()){
           if ($site!=='.'&&$site!==".."&&is_dir($sitePath.DIRECTORY_SEPARATOR.$site)){
               if ($js_name=="51la_js"){
                   file_put_contents($sitePath.DIRECTORY_SEPARATOR.$site.DIRECTORY_SEPARATOR.'51la.js',$js_content);
               }elseif ($js_name=='adv_js'){
                   if (!is_dir($sitePath.DIRECTORY_SEPARATOR.$site.DIRECTORY_SEPARATOR.'js')){
                       mkdir($sitePath.DIRECTORY_SEPARATOR.$site.DIRECTORY_SEPARATOR.'js',0777,true);
                   }
                   file_put_contents($sitePath.DIRECTORY_SEPARATOR.$site.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'adv.php',$js_content);
               }
           }
       }
    }









}
