<aside class="main-sidebar">
    <section class="sidebar" style="height: auto;">
        <!--会员面版-->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="{{'/images/admin/user2019.jpg'}}" class="img-circle" alt="我的头像">
            </div>
            <div class="pull-left info">
                 <p>{{Auth::guard('admin')->user()->display_name}}</p>
                <a href="#" title="在线">
                    <i class="fa fa-circle text-success"></i>online
                </a>
            </div>
        </div>
        <!--会员面版-->
        <!--三级菜单-->
        <ul id="nav" class="sidebar-menu tree" data-widget="tree">
            <li class="treeview">
                <a href="javascript:" onclick="addTabsLocal('dashboard','我的桌面','{{route('dashboard')}}',this);">
                    <i class="fa fa-home"></i>
                    <span>我的桌面</span>
                </a>
            </li>
            @foreach(RBAC::getMenuslv3() as $menu)
                <li class="treeview">
                    <a href="#">
                        {!!$menu['icon_html']!!}
                        <span>{{ $menu['title']}}</span>
                        @if(isset($menu['subMenu']))
                            <!--有二级菜单显示向下箭头-->
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        @endif
                    </a>
                    @if(isset($menu['subMenu']))
                        <ul class="treeview-menu">
                            @foreach($menu['subMenu'] as $lv2)
                                <li>
                                    @if(isset($lv2['subMenulv3']))
                                        <!--有三级子菜单-显示向下箭头-->
                                        <a href="javascript:">
                                            <i class="fa fa-play-circle-o"></i> <span>{{$lv2['title']}}</span>
                                            <span class="pull-right-container">
                                                <i class="fa fa-angle-left pull-right"></i>
                                            </span>
                                        </a>
                                        <!--显示三级菜单-->
                                        <ul class="treeview-menu">
                                            @foreach($lv2['subMenulv3'] as $lv3)
                                                <li>
                                                    <a class="ml1" href="javascript:" onclick="addTabsLocal('{{$lv3['name']}}','{{$lv3['title']}}','{{$lv3['href']}}',this);">
														<i class="fa fa-minus-circle"></i> <span>{{$lv3['title']}}</span>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <!--无三级子菜单-显示菜并增加点击事件-->
                                        <a href="javascript:" onclick="addTabsLocal('{{$lv2['name']}}','{{$lv2['title']}}','{{$lv2['href']}}',this);">
                                            <i class="fa fa-stop-circle-o"></i> <span>{{$lv2['title']}}</span>
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>

    </section>
</aside>
