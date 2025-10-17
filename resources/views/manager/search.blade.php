@if(!empty($page_title))
    @if('Search'== $page_title['type'])
        @include($page_title['content'])
    @elseif('Title'== $page_title['type'])
        <div></div>
    @else
        
    @endif
@endif