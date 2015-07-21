<!-- Modal -->
<div class="modal fade" id="chequeSignatorModal" tabindex="-1" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
					&times;
				</button>
                <h4 class="modal-title" id="myModalLabel"><i class="fa fa-lg fa-user"></i>Set Cheque Signator</h4>
			</div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group">
                        <label class="col-md-2 control-label">Signator*</label>
                        <div class="col-md-10">
                            <select class="form-control" id="batchSelectChequeSignator" autocomplete="off">
                                <option selected disabled>Select a User</option>
                                @foreach($chequesign_users as $user_id => $user_name)
                                    <option value="{{$user_id}}">{{$user_name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="buttonn" class="btn btn-success" id="btn-saveChequeSignator"><span class="glyphicon glyphicon-floppy-disk"></span> Save</button>
                    </div>
                </form>
            </div> <!-- modal-body -->
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->