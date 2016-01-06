<?php
/**
 * Created by PhpStorm.
 * User: kpudaruth
 * Date: 06/01/2016
 * Time: 11:12
 */

namespace Swift\Admin;


class EeziOrder
{
    /**
     * Generate product list excel sheet for eezi order as requested by Anglo African
     *
     * @param $job
     * @param array $data
     * @return bool
     */
    public function generateProductExcelSheet($job,$data)
    {
        $user = \Sentry::findUserByID($data['user_id']);
        if(!$user) {
            \Log::error('Unable to find user with ID: '.$data['user_id'].' for eezi order excel sheet generation');
        }

        $products = \DB::table('sct_jde.jdeitems')
                    ->whereIn('GLPT',['FOOD','HHPC','WINE'])
                    ->orderBy('ITM')
                    ->get();

        if(count($products) === 0) {
            \Mail::queueOn(\Config::get('queue.connections.sqs-mail.queue'),
                'emails.admin.template',
                ['note'=>'Excel sheet was not generated, since no products was found in SCT JDE database'],
                function($message) use ($user) {
                    $message->to($user->email,$user->first_name.' '.$user->last_name)
                        ->subject('Eezi Order - Product excel sheet - Failed');
                }
            );
            $job->delete();
            return false;
        }

        $objPHPExcel = new \PHPExcel();

        $objPHPExcel->getProperties()
            ->setCreator("Scott Swift")
            ->setLastModifiedBy("Scott Swift")
            ->setTitle("Eezi Order Product List")
            ->setSubject("Eezi Order Product List")
            ->setDescription("Product list extracted from JDE for eezi order")
            ->setKeywords("scott swift eezi order product list")
            ->setCategory("Scott Swift - Eezi Order Product List");

        //Add Header Data
        $headers = array('A'=>'AITM','B'=>'Name','C'=>'Bar Code','D'=>'Category','E'=>'Base Sales Price');
        foreach($headers as $key => $header)
        {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($key.'1',$header);
        }

        $count = 2;

        //Fetch price for each product
        foreach($products as $p)
        {
            $costPrice = \JdeSales::getProductLatestCostPrice($p->ITM);
            if($costPrice) {
                $p->costPrice = $costPrice->UPRC;
            } else {
                $p->costPrice = 0;
            }

            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValueExplicit('A'.$count,$p->AITM)
                ->setCellValueExplicit('B'.$count,$p->DSC1)
                ->setCellValueExplicit('C'.$count,$p->DSC2)
                ->setCellValueExplicit('D'.$count,$p->SRP3)
                ->setCellValueExplicit('E'.$count,$p->costPrice);

            $objPHPExcel->getActiveSheet()->getStyle("A$count:E$count")
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);

            $count++;
        }

        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('Product List '.date('Y-m-d h.m'));

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');


        $tmp_handle = fopen('php://temp', 'r+');
        $objWriter->save($tmp_handle);
        rewind($tmp_handle);
        $excelOutput = stream_get_contents($tmp_handle);

        //Create mail
        \Mail::send('emails.admin.template',
            ['note'=>'Excel sheet has been successfully generated for '.count($products).' product(s).'],
            function ($message) use ($user,$excelOutput) {
                $message->to($user->email,$user->first_name.' '.$user->last_name)
                    ->subject('Eezi Order - Product excel sheet - Success')
                    ->attachData($excelOutput,"Eezi Order - Product List Extract JDE - ".date('Y-m-d h.m').".xls");
            }
        );

        $job->delete();
    }
}