<table class="table table-bordered table-hover">
    @if(!empty($lateNodes))
        <tr>
            <th>
                Module Name
            </th>
            <th>
                Health
            </th>
            <th>
                Late Tasks
            </th>
            <th>
                Total Pending Tasks
            </th>
        </tr>    
        @foreach($lateNodes as $k=>$l)

            <tr>
                <td>
                    <a class="pjax" href="/{{ $k }}"><i class="fa {{ $l['icon'] }}"></i> {{ $l['name'] }}</a>
                </td>
                <td>
                    {{ \Helper::systemHealth($l['late_count'],$l['late_total_count']) }}
                </td>
                <td>
                    {{ $l['late_count'] }}
                </td>
                <td>
                    {{ $l['late_total_count'] }}
                </td>
            </tr>
        @endforeach
    @else
        <tr>
            <td><h2>No information so far.</h2></td>
        </tr>
    @endif
</table>