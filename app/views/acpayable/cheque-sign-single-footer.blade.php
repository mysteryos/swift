            <tr>
                <td colspan='2'><b>Total: </b><td>
                <td>{{$pay->invoice->currencyRelation->code}} {{number_format($total)}}</td>
            </tr>
        </table>
        <div class="row">
            <div class="col-xs-12">
                <button class="btn btn-default btn-sign-cheque btn-sm col-xs-5 col-xs-offset-3" tabindex="-1" type="submit"><i class="fa fa-check hide"></i> Sign Cheque</button>
            </div>
        </div>
    </div>
</div>
</form>
</div>