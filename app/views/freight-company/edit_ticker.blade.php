@if(count($ticker))
    <table class="table table-hover table-responsive">
        @foreach($ticker as $t)
        <?php 
            $revisionHistory = $t->revisionHistory()->get()->all();
            usort($revisionHistory,function($a,$b){
                return $b->created_at > $a->created_at;
            });
            foreach($revisionHistory as $r)
            {
                if($r->key == "freight_company_id" && $r->new_value != "")
                {
                    $freightCompanyUpdateDate = $r->updated_at;
                }
            }
            if(!isset($freightCompanyUpdateDate))
            {
                $freightCompanyUpdateDate = $t->created_at;
            }
        ?>
            <tr>
                <td>
                    <abbr title="{{date("Y/m/d H:i",strtotime($freightCompanyUpdateDate))}}" data-livestamp="{{strtotime($freightCompanyUpdateDate)}}"></abbr>
                </td>
                <td>
                    <?php 
                        switch($t->freight_type)
                        {
                            case SwiftFreight::TYPE_AIR:
                                echo '<i class="fa fa-lg fa-plane" title="air"></i>';
                                break;
                            case SwiftFreight::TYPE_LAND:
                                echo '<i class="fa fa-lg fa-truck" title="land"></i>';
                                break;
                            case SwiftFreight::TYPE_SEA:
                                echo '<i class="fa fa-lg fa-anchor" title="sea"></i>';
                            default:
                                echo '<i class="fa fa-lg fa-question" title="unknown"></i>';
                                break;
                        }
                    ?>
                </td>
                <td>
                    <span>
                        {{ Helper::getOrderTrackingLink($t->order)."'s freight" }}&nbsp;<?php 
                        if($t->freight_eta != "" && $t->freight_etd != "" && Carbon::now()->diffInSeconds($t->freight_etd, false) < 0 && Carbon::now()->diffInSeconds($t->freight_eta, false) > 0)
                        {
                            echo "is in <span class=\"txt-color-orange\">transit</span>";
                        }
                        elseif($t->freight_eta != "" && Carbon::now()->diffInSeconds($t->freight_eta, false) < 0)
                        {
                            echo "has already <span class=\"txt-color-green\">arrived</span>";
                        }                        
                        elseif($t->freight_etd != "" && Carbon::now()->diffInSeconds($t->freight_etd,false) > 0)
                        {
                            echo "has yet to <span class=\"txt-color-red\">depart</span>";
                        }
                        else
                        {
                            echo "is of <span class=\"txt-color-red\">unknown status</span>";
                        }
                        ?>
                    </span>
                </td>
            </tr>
        @endforeach
    </table>
@else
<table class="table table-hover table-responsive">
    <tr>
        <td class="text-align-center"><h3>No freights by this company yet</h3></td>
    </tr>
</table>
@endif