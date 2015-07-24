<!-- Modal -->
<div class="modal fade" id="assignDispatchModal" tabindex="-1" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
					&times;
				</button>
                <h4 class="modal-title" id="myModalLabel"><i class="fa fa-lg fa-envelope"></i>Set Dispatch</h4>
			</div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group">
                        <label class="col-md-2 control-label">Dispatch by*</label>
                        <div class="col-md-10">
                            <select class="form-control" id="batchSelectDispatch" autocomplete="off">
                                <option disabled selected>Select a method</option>
                                @foreach($dispatch_method as $dispatch_key => $dispatch_val)
                                    <option value="{{$dispatch_key}}">{{$dispatch_val}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" id="btn-saveDispatch"><span class="glyphicon glyphicon-floppy-disk"></span> Save</button>
                    </div>
                </form>
            </div> <!-- modal-body -->
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->