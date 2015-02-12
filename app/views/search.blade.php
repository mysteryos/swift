@extends('layout')

@section('content')

<!-- RIBBON -->
<div id="ribbon">

        <div class="ribbon-button-alignment">
            <!--<a class="btn btn-default" href="javascript:void(0);"><i class="fa fa-gear"></i> Icon Random</a>-->
<!--            <span id="search" class="btn btn-ribbon hidden-xs" data-title="search"><i class="fa fa-grid"></i> Change Grid</span>
            <span id="add" class="btn btn-ribbon hidden-xs" data-title="add"><i class="fa fa-plus"></i> Add</span>
            <span id="search" class="btn btn-ribbon" data-title="search"><i class="fa fa-search"></i> <span class="hidden-mobile">Search</span></span>-->
        </div>

</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content" data-js="elastic_search">

    <!-- row -->

    <div class="row">

            <div class="col-sm-12">

                    <ul id="myTab1" class="nav nav-tabs bordered">
                            <li class="active">
                                    <a href="#s1" data-toggle="tab">Search All</a>
                            </li>
                            <li class="pull-right hidden-mobile">
                                    <a href="javascript:void(0);"> <span class="note">About {{ $hits_count }} results ({{ $time_taken }} seconds) </span> </a>
                            </li>
                    </ul>

                    <div id="myTabContent1" class="tab-content bg-color-white padding-10">
                            <div class="tab-pane fade in active" id="s1">
                                    <h1> Search <span class="semi-bold">{{ $selected_category_text }}</span></h1>
                                    <br>
                                    <form name="search" action="/search" type="GET" id="search_again">
                                        <div class="row hidden-mobile">
                                            <div class="col-xs-3">
                                                <select name="category" class="form-control input-lg">
                                                    <option value="everything">Everything</option>
                                                    @if(count($category))
                                                        @foreach($category as $ck=>$cv)
                                                            <option value="{{ $ck }}" @if($selected_category == $ck) {{ " selected" }} @endif>{{ $cv }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="col-xs-7 no-padding">
                                                <input class="form-control input-lg" name="search" type="text" placeholder="Search again..." id="search-project" value="{{ $query }}">
                                            </div>
                                            <div class="col-xs-2 no-padding">
                                                <button type="submit" class="btn btn-default btn-lg">
                                                        &nbsp;&nbsp;&nbsp;<i class="fa fa-fw fa-search fa-lg"></i>&nbsp;&nbsp;&nbsp;
                                                </button>
                                            </div>
                                        </div>
                                    </form>                                    


                                    <h1 class="font-md"> Search Results for <span class="semi-bold">{{ $query or "(everything)" }}</span><small class="text-danger"> &nbsp;&nbsp;(@if($hits_count !== 0) {{ $result->getFrom()." - ".$result->getTo()." of " }}@endif{{ $hits_count }} results)</small></h1>

                                    @if($hits_count === 0)
                                        <div class="text-center">
                                            <h2>No results found for '{{ $query }}'</h2>
                                        </div>
                                    @else
                                        @foreach($result as $r)
                                            <div class="search-results clearfix">
                                                    <h4><a class="pjax" href="{{ $r['url'] }}">{{'<i class="fa fa-lg '.$r['icon'].'"></i>'}}&nbsp;{{ $r['value'] }}</a></h4>
                                                    <div>
                                                            <div class="url text-success">
                                                                Forms > {{ $r['title'] }}
                                                            </div>
    <!--                                                        <p class="note">
                                                                Last update By 
                                                            </p>-->
                                                            <p class="description">
                                                                {{ $r['highlight'] }}
                                                            </p>
                                                    </div>
                                            </div>                                        
                                        @endforeach
                                    @endif

                                    <div class="text-center">
                                            <hr>
                                            {{ $result->appends(array('search' => $query,'category'=>$selected_category))->links('pagination.slider-pjax') }}
                                            <br>
                                            <br>
                                            <br>
                                    </div>

                            </div>
                    </div>

            </div>

    </div>

    <!-- end row -->
</div>

@stop