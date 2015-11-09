                <tr class="pointable invoice-row">
                    <td><a class="pjax" href="{{\Helper::generateURL($pay->acp)}}">#{{$pay->acp->id}}</a>
                        @if(count($pay->acp->document))
                            <ul class="hide doc-list" id="doc-list-{{$pay->id}}">
                                @foreach($pay->acp->document as $k => $doc)
                                    <li data-href="/pdfviewer/viewer.html?file={{urlencode($doc->getAttachedfiles()['document']->url())}}" @if($k===0)class="doc-selected"@endif>
                                        <div class="doc-icon">
                                                <?php
                                                switch($doc->getAttachedfiles()['document']->contentType())
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
                                                ?>
                                        </div>
                                        <div class="doc-name">
                                            {{$doc->getAttachedfiles()['document']->originalFilename()}}
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                        <input type="hidden" name="pv_id[]" value="{{\Crypt::encrypt($pay->id)}}" />
                    </td>
                    <td>{{$pay->invoice->number}}</td>
                    <td>@if($pay->invoice->date){{$pay->invoice->date->format('d/m/Y')}}@endif</td>
                    <td>{{$pay->amount_paid}}</td>
                </tr>