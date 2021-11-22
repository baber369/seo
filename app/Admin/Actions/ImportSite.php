<?php

namespace App\Admin\Actions;

use App\Jobs\GenSiteJob;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;


class ImportSite extends Action
{
    protected $selector = '.import-post';

    public function handle(Request $request)
    {
        $file = $request->file('file');
        if (!$file->isValid()) {
            return $this->response()->error("无效文件")->refresh();
        }
        $excel = IOFactory::load($file->getRealPath());
        $workSheet = $excel->getActiveSheet();
        $rowsCount = $workSheet->getHighestRow();
        if ($rowsCount <= 0) {
            return $this->response()->error("没有站点数据")->refresh();
        }
        for ($row = 1; $row <= $rowsCount; $row++) {
            $domain = $workSheet->getCellByColumnAndRow(1, $row)->getValue();
            $origin_site = $workSheet->getCellByColumnAndRow(2, $row)->getValue();
            $title = $workSheet->getCellByColumnAndRow(3, $row)->getValue();
            $keywords = $workSheet->getCellByColumnAndRow(4, $row)->getValue();
            $description = $workSheet->getCellByColumnAndRow(5, $row)->getValue();
            $search = $workSheet->getCellByColumnAndRow(6, $row)->getValue();
            $replace = $workSheet->getCellByColumnAndRow(7, $row)->getValue();
            if (!$domain||!$origin_site){
                return $this->response()->error("第{$row}行域名或原站地址未配置")->refresh();
            }
            dispatch(new GenSiteJob($domain,$origin_site,$title,$keywords,$description,$search,$replace));
        }
        return $this->response()->success('Success message...')->refresh();
    }

    public function form()
    {
        $this->file('file', '请选择文件')->required();
    }

    public function html()
    {
        return <<<HTML
        <a class="btn btn-sm btn-info import-post">导入数据</a>
HTML;
    }
}
