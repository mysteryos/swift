<div class="panel panel-default pv-row" data-formId="{{$pay->id}}">
    <form class="cs-form" action="/{{$rootURL}}/save-cheque-sign-exec" method="POST">
        <div class="panel-heading">
            <h4 class="panel-title">
                <a href="#form-{{$pay->id}}" data-toggle="collapse" tabindex="-1">
                    <i class="fa fa-lg fa-angle-down pull-right"></i><i class="fa fa-lg fa-angle-up pull-right"></i>
                    <b>{{$pay->acp->supplier_name}}</b><br/>
                    Payment Number: {{$pay->payment_number}} <a href="/accounts-payable/view-payment/{{$pay->payment_number}}" class="colorbox-ajax"><i class="glyphicon glyphicon-search"></i>View Payment</a>
                </a>
            </h4>
        </div>
        <div id="form-{{$pay->id}}" class="panel-collapse collapse in">
            <div class="panel-body">
                <table class="table table-hover">
                    <tr>
                        <th>
                            Form
                        </th>
                        <th>
                            Invoice
                        </th>
                        <th>
                            Date
                        </th>
                        <th>
                            Amount
                        </th>
                        <th>
                            Approvers
                        </th>
                    </tr>