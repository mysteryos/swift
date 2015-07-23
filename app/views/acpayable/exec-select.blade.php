<!-- Modal -->
<div class="modal fade" id="assignExecModal" tabindex="-1" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
					&times;
				</button>
                <h4 class="modal-title" id="myModalLabel"><i class="fa fa-lg fa-user"></i>Set Executive</h4>
			</div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group">
                        <label class="col-md-2 control-label">Executive*</label>
                        <div class="col-md-10">
                            <select class="form-control" id="batchSelectExec" autocomplete="off">
                                <option disabled selected>Select a user</option>
                                @foreach($exec_users as $user_key => $user_name)
                                    <option value="{{$user_key}}">{{$user_name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" id="btn-saveExec"><span class="glyphicon glyphicon-floppy-disk"></span> Save</button>
                    </div>
                </form>
            </div> <!-- modal-body -->
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->