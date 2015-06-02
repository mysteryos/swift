<?php
/*
 * Name: Product Returns TCPDF
 * Description: Prints Pickup Form
 */

namespace Swift\PR;

class PrTcpdf extends \TCPDF{
    protected $returnId;
    protected $form;
    //Page header
    public function Header() {
            // Logo
            $image_file = '/img/scott_consumer_logo.png';
            $this->Image($image_file, 15, 10, 45, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            // Set font
            $this->SetFont('times', "", 8);
            //Scott Company Info
            $companyinfo_html = "<p>Scott & Co Ltd</p>
                                 <p>Industrial Park 1, Riche-Terre, Mauritius</p>
                                 <p><b>T</b> (230) 206 9400 <b>F</b> (230) 248 9401 <b>www.scott.mu</b></p>
                                 <p><b>BRN C0600577</b></p>";
            $tagvs = array('p' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)));
            $this->setHtmlVSpace($tagvs);
            $this->writeHTMLCell(0,0,45,23,$companyinfo_html);


            //Customer Info
            $customerinfo_html = "<table cellspacing=\"3\">
                                    <tr>
                                        <td width=\"60%\"><b>Customer Name:</b> {$this->form->customer_name}</td>
                                        <td><b>Date:</b> ".date('d.m.Y')."</td>
                                    </tr>
                                    <tr>
                                        <td><b>Customer Code:</b> {$this->form->customer_code}</td>
                                        <td><b>Salesman:</b> {$this->form->owner_name}</td>
                                    </tr>
                                    <tr>
                                        <td><b>Pickup Driver:</b> ".(count($this->form->pickup) ? $this->form->pickup->first()->driver_name : "N/A")."</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                  </table>";
            $this->setFont('times','',10);
            $this->writeHTMLCell(0, 0, 15, 60, $customerinfo_html);

            //ID of Form;

            // Set Form Id
            $this->SetFont('times',"",12);
            $this->writeHTMLCell(0,0,150,30,"<span><b>No.</b></span>");

            $this->SetFont('helvetica',"",20);
            $this->writeHTMLCell(0,0,160,28.5,"<span>".sprintf('%07u', $this->form->id)."</span>");

            //Title of Form
            $this->RoundedRect(55, 45, 100, 8, 3, '1111','DF',null,array(0,0,0));
            $this->SetTextColor(255,255,255);
            $this->SetFont('helvetica',"B",12);
            $this->Text(62, 46, 'REQUEST FORM FOR RETURN OF GOODS');

            $this->SetTopMargin(90);
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getPageNumGroupAlias().' / '.$this->getPageGroupAlias(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

        //Salesman Info/Signature
        $salesmaninfo_html = "<br/><br/><br/>
                                <table cellpadding=\"5\">
                                    <tr>
                                        <td>
                                            <table border=\"1\">
                                                <tr style=\"line-height:30px;\">
                                                    <th>
                                                        To be filled by Driver at time of pickup
                                                    </th>
                                                </tr>
                                                <tr style=\"line-height:15px;\">
                                                    <td>  Received By(Name of Driver):   .........................................................................................</td>
                                                </tr>
                                                <tr style=\"line-height:15px;\">
                                                    <td>  Vehicle No:   .........................................................................................</td>
                                                </tr>
                                                <tr style=\"line-height:15px;\">
                                                    <td>  Reception Date:   .........................................................................................</td>
                                                </tr>
                                              </table>
                                        </td>
                                        <td>
                                            <table border=\"1\">
                                                <tr style=\"line-height:30px;\">
                                                    <th>
                                                        To be filled by Reception Officer of Scott
                                                    </th>
                                                </tr>
                                                <tr style=\"line-height:15px;\">
                                                    <td>  Received By: .........................................................................................</td>
                                                </tr>
                                                <tr style=\"line-height:15px;\">
                                                    <td>  Reception Date: .........................................................................................</td>
                                                </tr>
                                                <tr style=\"line-height:15px;\">
                                                    <td>  Signature: .........................................................................................</td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <table border=\"1\">
                                                <tr style=\"line-height:30px;\">
                                                    <th>
                                                        To be filled by Client at time of pickup
                                                    </th>
                                                </tr>
                                                <tr style=\"line-height:15px;\">
                                                    <td>  Delivery Date:   ........................................................................................</td>
                                                </tr>
                                                <tr style=\"line-height:15px;\">
                                                    <td>  Client's Representative Name:   ........................................................................................</td>
                                                </tr>
                                                <tr style=\"line-height:15px;\">
                                                    <td>  Client's Representative Signature:   ........................................................................................</td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td>
                                            &nbsp;
                                        </td>
                                    </tr>
                                </table>
                                ";

        $this->writeHTMLCell(0, 0, null, 180, $salesmaninfo_html);
        $this->SetFooterMargin(300);
    }

    public function setFormData($formObj)
    {
        $this->form = $formObj;
    }
}

