<fieldset>
    <legend>General Info</legend>
    <div class="form-group">
        <label class="col-md-2 control-label">Email</label>
        <div class="col-md-10">
            <p class="form-control-static">{{ $salesman->user->email }}</p>
        </div>
    </div>    
    <div class="form-group">
        <label class="col-md-2 control-label">Department</label>
        <div class="col-md-10">
            <p class="form-control-static">{{ $salesman->department->name or "(Not set)" }}</p>
        </div>
    </div>
    
    <legend>Clients</legend>
    <div class="form-group">
        <div class="col-xs-12">
            <table class="table table-hover table-borderless">
                <tr>
                    <th>
                        Code
                    </th>
                    <th>
                        Customer Name
                    </th>
                    <th>
                        Location
                    </th>
                </tr>
                @if(count($salesman->client))
                    @foreach($salesman->client as $c)
                    <tr>
                        <td>
                            {{ $c->customer_code }}
                        </td>
                        <td>
                            {{ $c->customer->ALPH }}
                        </td>
                        <td>
                            {{ $c->customer->CTY1 }}
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="3">
                            <h1>No clients found for salesman. Perhaps you should contact your administrator.</h1>
                        </td>
                    </tr>                    
                @endif
            </table>
        </div>
    </div>
</fieldset>


