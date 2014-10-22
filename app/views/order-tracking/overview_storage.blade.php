@if(isset($order_storage) && !empty($order_storage))
    <table class="table table-responsive table-hover table-striped">
        @foreach($order_storage as $os)
            <?php 
                $diffWithNow = Carbon::now()->diffInDays($os['storage_start'],false);
                if($diffWithNow > 2)
                {
                    $class = "storage_ok";
                }
                elseif ($diffWithNow < 0)
                {
                    $class = "storage_bad";
                }
                else
                {
                    $class = "storage_urgent";
                }
            ?>
            <tr data-href="/order-tracking/view/{{ Crypt::encrypt($os['order']->id) }}">
                <td class="{{ $class }}">
                    <i class="fa <?php
                        switch($class)
                        {
                            case 'storage_ok':
                                echo "fa-check text-color-green";
                                break;
                            case 'storage_bad':
                                echo "fa-exclamation-triangle text-color-red";
                                break;
                            case 'storage_urgent':
                                echo "fa-exclamation text-color-orange";
                                break;
                        }
                        ?>"></i>&nbsp;<abbr title="{{$os['storage_start']->toDateString()}}" data-livestamp="{{strtotime($os['storage_start']->toDateTimeString())}}"></abbr>
                </td>
                <td>
                    {{ $os['freight']->vessel_name." - ".$os['freight']->vessel_voyage }}
                </td>
                <td>
                    Cost - Rs. {{ Helper::calculateStorageCost($os['storage_start'],count($os['order']->shipment))*floatval(trim($os['rate'])) }}
                </td>
                <td>
                    <p><a href="/order-tracking/view/{{ Crypt::encrypt($os['order']->id) }}" class="pjax"><strong>{{ $os['order']->name." (ID: ".$os['order']->id.")" }}</strong></a></p>
                </td>
            </tr>
        @endforeach
    </table>
@else
    <h1 class="text-align-center"><i class="fa fa-smile-o"></i> No upcoming storage costs for now.</h1>
@endif