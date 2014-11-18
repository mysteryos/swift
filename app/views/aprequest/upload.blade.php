@if(count($doc))
    @foreach($doc as $d)
        <div class="row dz-success" data-id="{{Crypt::encrypt($d->id)}}" data-name="{{$d->getAttachedfiles()['document']->originalFilename()}}">
            <div class="row">
                <div class="col-xs-12">
                    <div class="hide">
                        <span class="preview"><img data-dz-thumbnail=""></span>
                    </div>
                    <div class="col-xs-6">
                        <span class="name" data-dz-name=""><a class="file-view" href="{{$d->getAttachedfiles()['document']->url()}}" rel="tooltip" data-original-title="Last update: {{$d->getAttachedfiles()['document']->updatedAt()}} &#013; Updated By: {{Helper::getUserName($d->user_id,Sentry::getUser())}}" data-placement="bottom">
                        <?php 
                        switch($d->getAttachedfiles()['document']->contentType())
                        {
                            case "image/jpeg":
                            case "image/png":
                            case "image/bmp":
                            case "image/jpg":
                                echo '<i class="fa fa-file-image-o"></i>';
                                break;
                            case "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet":
                            case "application/vnd.ms-excel":
                                echo '<i class="fa fa-file-excel-o"></i>';
                                break;
                            case "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
                            case "application/msword":
                                echo '<i class="fa fa-file-word-o"></i>';
                                break;
                            case "application/pdf":
                                echo '<i class="fa fa-file-pdf-o"></i>';
                                break;                        
                            default:
                                echo '<i class="fa fa-file-o"></i>';
                                break;                
                        }
                        ?>{{$d->getAttachedfiles()['document']->originalFilename()}}</a></span>
                        <strong class="error text-danger" data-dz-errormessage=""></strong>
                    </div>
                    <div class="col-xs-4">
                        <p class="size hide" data-dz-size=""></p>
                        <div class="progress progress-striped active hide" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                          <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress=""></div>
                        </div>
                    </div>
                    <div class="col-xs-2 @if(!$isCreator && !$isAdmin) hide @endif">
                      <button data-dz-remove="" class="btn btn-danger delete btn-xs">
                        <i class="glyphicon glyphicon-trash"></i>
                      </button>
                    </div>   
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <div class="col-xs-10">
                        <i class="fa fa-tags"></i>&nbsp;
                                <a data-title="Select Tags" data-value="<?php 
                                    if(count($d->tag))
                                    {
                                        $tag = array();
                                        foreach($d->tag as $t)
                                        {
                                            $tag[] = $t->type;
                                        }
                                        echo implode(",",$tag);
                                    }

                                ?>" data-source='{{ $tags }}' data-emptytext="No tags" data-name="type" data-mode="popup" data-placement="bottom" data-type="checklist" data-url="/order-tracking/tag" data-pk="{{Crypt::encrypt($d->id)}}" class="editable tags"></a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif

@if(isset($dummy))
<div id="template" class="row">
    <!-- This is used as the file preview template -->
    <div class="hide">
        <span class="preview"><img data-dz-thumbnail=""></span>
    </div>
    <div class="col-xs-6">
        <div class="row">
            <div class="col-xs-12">        
                <span class="name" data-dz-name=""></span>
                <strong class="error text-danger" data-dz-errormessage=""></strong>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <i class="fa fa-tags"></i>&nbsp;
                <a data-title="Select Tags" data-value="" data-emptytext="No tags" data-name="type" data-mode="popup" data-source='{{ $tags }}' data-type="checklist" data-url="/order-tracking/tag" data-pk="0" class="editable tag dummy hide"></a>
            </div>
        </div>
    </div>
    <div class="col-xs-4">
        <p class="size hide" data-dz-size=""></p>
        <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
          <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress=""></div>
        </div>
    </div>
    <div class="col-xs-2">
      <button data-dz-remove="" class="btn btn-danger delete btn-xs">
        <i class="glyphicon glyphicon-trash"></i>
      </button>
    </div>
</div>
@endif