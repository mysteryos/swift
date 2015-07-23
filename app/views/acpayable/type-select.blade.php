<!-- Modal -->
<div class="modal fade" id="paymentTypeModal" tabindex="-1" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
					&times;
				</button>
                <h4 class="modal-title" id="myModalLabel"><i class="fa fa-lg fa-info"></i>Set Type</h4>
			</div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group">
                        <label class="col-md-2 control-label">Type*</label>
                        <div class="col-md-10">
                            <select class="form-control" id="batchSelectType" autocomplete="off">
                                <option disabled selected>Select a type</option>
                                @foreach($payment_type as $type_key => $type_val)
                                    <option value="{{$type_key}}">{{$type_val}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" id="btn-saveType"><span class="glyphicon glyphicon-floppy-disk"></span> Save</button>
                    </div>
                </form>
            </div> <!-- modal-body -->
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->