<div id="acp-list" data-load="/accounts-payable/list-by-form/{{get_class($order)}}/{{\Crypt::encrypt($order->getKey())}}">
    @include('order-tracking.edit_payable_list')
</div>

<!-- Modal -->
<div class="modal fade" id="payableFormModal" tabindex="-1" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
					&times;
				</button>
                <h4 class="modal-title" id="myModalLabel"><i class="fa fa-lg fa-money"></i> Create Accounts Payable</h4>
			</div>
            <form action="/accounts-payable/save-by-form" name="payable_form" method="POST" class="form-horizontal" id="payableForm">
                <input type="hidden" name="payable_type" value="{{get_class($order)}}" />
                <input type="hidden" name="payable_id" value="{{\Crypt::encrypt($order->getKey())}}" />
                <div class="modal-body">
                    <div class="form-group">
                        <label class="col-md-2 control-label">Type*</label>
                        <div class="col-md-10">
                            <select class="form-control" name="type">
                                <option selected disabled>Please select an option</option>
                                @foreach($payable_charges as $k=>$p)
                                    <option value="{{$k}}">{{$p}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Payment Voucher number*</label>
                        <div class="col-md-10">
                             <input type="text" autocomplete="off" class="form-control" name="pv_number" placeholder="Type in a payment voucher number" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Supplier*</label>
                        <div class="col-md-10">
                            <input type="hidden" class="full-width" id="suppliercode" name="supplier_code" placeholder="Type in the supplier's name/code" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Billable Company*</label>
                        <div class="col-md-10">
                            <input type="hidden" class="full-width" id="companycode" name="company_code" placeholder="Type in billable company code/name" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Invoice number*</label>
                        <div class="col-md-10">
                             <input type="text" autocomplete="off" class="form-control" name="invoice_number" placeholder="Type in an invoice number" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-2 control-label">Purchase Order number</label>
                        <div class="col-xs-8">
                                <input type="text" autocomplete="off" class="form-control" name="po_number" placeholder="Type in a purchase order number" />
                        </div>
                        <div class="col-xs-2">
                            <select name="po_type" class="form-control">
                                @foreach(\SwiftPurchaseOrder::$types as $k => $v)
                                    <option value="{{$k}}" @if($v==="ON")selected @endif>{{$v}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="btn-savePayableForm"><span class="glyphicon glyphicon-floppy-disk"></span> Save</button>
                </div>
            </form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->