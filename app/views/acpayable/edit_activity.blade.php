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
                            echo " <span class=\"activity-add\">created</span> <i>".(is_callable($a->className()) ? $a->className() : "Unknown")."</i> (ID: {$a->revisionable_id})</b>";
                            break;
                        case \Venturecraft\Revisionable\Revision::INSERT:
                            echo " <span class=\"activity-add\">added</span> <i>".(is_callable($a->fieldName()) ? ucfirst($a->fieldName()) : "Unknown")."</i> as <b>".(is_callable($a->newValue()) ? $a->newValue() : "Unknown")."</b>";
                            break;
                        case \Venturecraft\Revisionable\Revision::UPDATE:
                            echo " <span class=\"activity-change\">changed</span> <i>".(is_callable($a->fieldName()) ? ucfirst($a->fieldName()) : "Unknown")."</i> from <b>".(is_callable($a->oldValue()) ? $a->oldValue() : "Unknown")."</b> to <b>".(is_callable($a->newValue()) ? $a->newValue() : "Unknown")."</b>";
                            break;
                        case \Venturecraft\Revisionable\Revision::DELETE:
                            echo " <span class=\"activity-delete\">deleted</span> <i>".(is_callable($a->fieldName()) ? ucfirst($a->fieldName()) : "Unknown")."</i>, previously being <b>".(is_callable($a->oldValue()) ? $a->oldValue() : "Unknown")."</b>";
                            break;
                        case \Venturecraft\Revisionable\Revision::REMOVE:
                            echo " <span class=\"activity-delete\">removed</span> <i>".(is_callable($a->className()) ? $a->className() : "Unknown")."</i> (".(is_callable($a->primaryIdentifierName()) ? $a->primaryIdentifierName() : "Unknown").": ".(is_callable($a->primaryIdentifierValue()) ? $a->primaryIdentifierValue() : "Unknown").")</b>";
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
                        $related_obj = $related_model::withTrashed()->find($a->revisionable_id);
                        if($related_obj)
                        {
                            $rerevisionable = $related_obj->{$related_model->revisionPolymorphicIdentifier};
                            if(count($rerevisionable))
                            {
                                echo "[<i>".$rerevisionable->revisionClassName."</i> (".$rerevisionable->revisionPrimaryIdentifier.": ".$rerevisionable->{$rerevisionable->revisionPrimaryIdentifier}.")]";
                            }
                        }
                    }
                    ?>
                    </span>
                </td>
            </tr>
        @endforeach
    </table>
@endif