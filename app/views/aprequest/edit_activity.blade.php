@if(count($activity))
    <table class="table table-hover table-responsive">
        @foreach($activity as $a)
            <tr>
                <td>
                    <abbr title="{{date("Y/m/d H:i",strtotime($a->created_at))}}" data-livestamp="{{strtotime($a->created_at)}}"></abbr>
                </td>
                <td>
                    {{\Swift\Avatar::getHTML($a->user_id,true)}}
                </td>
                <td>
                    <span>
                    <?php 
                    switch($a->action)
                    {
                        case \Venturecraft\Revisionable\Revision::CREATE:
                            echo " <span class=\"activity-add\">created</span> <i>".$a->className()."</i> (ID: {$a->revisionable_id})</b>";
                            break;
                        case \Venturecraft\Revisionable\Revision::INSERT:
                            echo " <span class=\"activity-add\">added</span> <i>".ucfirst($a->fieldName())."</i> as <b>".$a->newValue()."</b>";
                            break;
                        case \Venturecraft\Revisionable\Revision::UPDATE:
                            echo " <span class=\"activity-change\">changed</span> <i>".ucfirst($a->fieldName())."</i> from <b>".$a->oldValue()."</b> to <b>".$a->newValue()."</b>";
                            break;
                        case \Venturecraft\Revisionable\Revision::DELETE:
                            echo " <span class=\"activity-delete\">deleted</span> <i>".ucfirst($a->fieldName())."</i>, previously being <b>".$a->oldValue()."</b>";
                            break;
                        case \Venturecraft\Revisionable\Revision::REMOVE:
                            echo " <span class=\"activity-delete\">removed</span> <i>".$a->className()."</i> (".$a->primaryIdentifierName().": ".$a->primaryIdentifierValue().")</b>";
                            break;
                        default:
                            echo " (error - unknown activity)";
                            break;
                    }
                    ?>
                    <?php 
                    $related_model = new $a->revisionable_type;
                    if(isset($related_model->revisionDisplayId) && isset($related_model->revisionDisplayId))
                    {
                        echo "[Id: $a->revisionable_id]";
                    }
                    
                    $poly = $related_model->revisionPolymorphicIdentifier;
                    if($poly)
                    {
                        $related_obj = $related_model::find($a->revisionable_id);
                        $rerevisionable = $related_obj->{$related_model->revisionPolymorphicIdentifier};
                        if(count($rerevisionable))
                        {
                            echo "[<i>".$rerevisionable->revisionClassName."</i> (".$rerevisionable->revisionPrimaryIdentifier.": ".$rerevisionable->{$rerevisionable->revisionPrimaryIdentifier}.")]";
                        }
                    }
                    ?>
                    </span>
                </td>
            </tr>
        @endforeach
    </table>
@endif