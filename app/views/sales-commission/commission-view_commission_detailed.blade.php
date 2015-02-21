<div class="smart-accordion-default panel-group panel-compressed">
    @foreach($commissions as $c)
    <div class="panel panel-default">
        <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" href="#commision_detailed_{{ $c->id }}" class="txt-color-darken">
                        <i class="fa fa-lg fa-angle-down pull-right"></i>
                        <i class="fa fa-lg fa-angle-up pull-right"></i>
                        <i class="fa fa-archive"></i> {{ $c->scheme_info_data->name }}
                    </a>
                </h4>
        </div>
        <div class="panel-collapse collapse in" id="commision_detailed_{{ $c->id }}">
            <div class="panel-body">
                <form class="form-horizontal">
                    <fieldset>
                        <legend>Scheme</legend>
                        <div class="form-group">
                            <label class="col-md-2 control-label">Rate</label>
                            <div class="col-md-10">
                                <p class="form-control-static">@if($c->rate_info === "" || $c->rate_info_data->rate == 0) N/A @else{{ number_format($c->rate_info_data->rate,2)."%" }}@endif</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label">Budget</label>
                            <div class="col-md-10">
                                <p class="form-control-static">@if($c->budget_info === "" || $c->budget_info_data->value == 0)  N/A @else{{ "Rs. ".number_format($c->budget_info_data->value) }}@endif</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label">Commission Value under this scheme</label>
                            <div class="col-md-10">
                                <p class="form-control-static">Rs. {{ number_format($c->total) }}</p>
                            </div>
                        </div>
                        <legend>Sales</legend>
                        <div class="col-xs-12">
                            <?php $currentorder = $total = 0; ?>
                                @foreach($c->product as $p)
                                    <?php $total += $p->total; ?>
                                    @if($p->jde_doc !== $currentorder)
                                        @if($currentorder !== 0)
                                            </table>
                                        @endif
                                    <table class="table table-hover table-condensed">
                                        <tr>
                                            <th colspan="3"><h2>{{ $p->customer->ALPH." (Code: ".$p->customer->AN8.")" }} | Order number: {{ $p->jde_doc }}</h2></th>
                                        </tr>
                                        <tr>
                                            <th class="col-xs-6">
                                                Product
                                            </th>
                                            <th class="col-xs-3">
                                                Qty
                                            </th>
                                            <th class="col-xs-3">
                                                Total
                                            </th>
                                        </tr>
                                        <?php $currentorder = $p->jde_doc; ?>                    
                                    @endif
                                        <tr>
                                            <td class="col-xs-6">{{ trim($p->jdeproduct->DSC1) }} - {{ $p->jdeproduct->AITM }}</td>
                                            <td class="col-xs-3">{{ $p->jde_qty }}</td>
                                            <td class="col-xs-3">Rs. {{ number_format($p->total) }}</td>                    
                                        </tr>
                                @endforeach
                            </table>
                            <table class="table table-borderless">
                                <tr>
                                    <td class="col-xs-9">
                                        <h2>Total Sales Value</h2>
                                    </td>
                                    <td class="col-xs-3">
                                        <h2>Rs. {{ number_format($total) }}</h2>
                                    </td>
                                </tr>
                            </table>                            
                        </div>
                    </fieldset>
                </form>                
            </div>
        </div>
    </div>
    @endforeach
</div>