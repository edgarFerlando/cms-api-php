@if($versions)
    <dl class="history-version-menu">
        @foreach($versions as $version) 
            <dt>{{ $version['alias'] }}</dt>
                @if($version['versions'])
                    @foreach($version['versions'] as $version_item)
                        <dd>&nbsp;&nbsp;<a class="{{ setActive(['admin/user/'.$version['user_id'].'/'.$version['route'].'/'.$version_item]) }}" href="{{ Route('admin.user.'.$version['route'].'.show', [ 'id' => $version['user_id'], 'version' => $version_item ]) }}">Version {{ $version_item }}</a></dd>
                    @endforeach
                @endif
        @endforeach
    </dl>
@endif