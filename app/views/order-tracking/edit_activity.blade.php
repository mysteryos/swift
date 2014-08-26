@if(count($activity))
    <table class="table table-hover table-responsive">
    @foreach($activity as $a)
        @if($a->oldValue() != "" || $a->newValue() != "")
        <tr>
            <td>
                <abbr title="{{date("Y/m/d H:i",strtotime($a->created_at))}}" data-livestamp="{{strtotime($a->created_at)}}"></abbr>
            </td>
            <td>
                {{\Swift\Avatar::getHTML($a->user_id,true)}}
            </td>
            <td>
                <span>@if($a->oldValue()=="")
                {{" <span class=\"activity-add\">added</span> <i>".$a->fieldName()."</i> as <b>".$a->NewValue()."</b>" }}
                @elseif($a->newValue()=="")
                {{" <span class=\"activity-delete\">deleted</span> <i>".$a->fieldName()."</i>, previously being <b>".$a->oldValue()."</b>"}}
                @else
                {{" <span class=\"activity-change\">changed</span> <i>".$a->fieldName()."</i> from <b>".$a->oldValue()."</b> to <b>".$a->newValue()."</b>"}}
                @endif</span>
            </td>
        </tr>
        @endif
    @endforeach
    </table>
@endif