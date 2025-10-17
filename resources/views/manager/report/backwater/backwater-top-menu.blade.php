<div class="example-box-wrapper col-lg-12 mb1" style="padding:0;">
    <ul class="nav-responsive nav nav-tabs">
        @foreach(RBAC::getPages('backwaterCalc') as $page)
            <li class="{{$page['class']}}">
                <a href="{{$page['href']}}">
                    <span class="badge pull-right"></span>
                    <i class="fa fa-sliders"></i>&nbsp;{{$page['display_name']}}
                </a>
            </li>
        @endforeach
    </ul>
</div>

