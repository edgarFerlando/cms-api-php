<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">
  <!-- sidebar: style can be found in sidebar.less -->
  <section class="sidebar">
    <!-- search form -->
    <form action="#" method="get" class="sidebar-form">
      <div class="input-group">
        <input type="text" name="q" class="form-control" placeholder="{{{ trans('app.cari') }}}..."/>
        <span class="input-group-btn">
          <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i></button>
        </span>
      </div>
    </form>
    <!-- /.search form -->
    <!-- sidebar menu: : style can be found in sidebar.less -->
    <ul class="sidebar-menu">
      <li class="{{ setActive('admin') }}"><a href="{{ url(getLang() . '/admin/dashboard') }}"> <i class="fa fa-dashboard"></i> <span>{{{ trans('app.dashboard') }}}</span>
      </a></li>
      <li class="{{ setActive('admin/menu*') }}"><a href="{{ url(getLang() . '/admin/menu') }}"> <i class="fa fa-bars"></i> <span>{{{ trans('app.menu') }}}</span> </a>
      </li>
            <!--<li class="treeview {{ setActive('admin/news*') }}"><a href="#"> <i class="fa fa-th"></i> <span>News</span>
                    <i class="fa fa-angle-left pull-right"></i> </a>
                <ul class="treeview-menu">
                    <li><a href="{{ url(getLang() . '/admin/news') }}"><i class="fa fa-calendar"></i> All News</a>
                    </li>
                    <li><a href="{{ url(getLang() . '/admin/news/create') }}"><i class="fa fa-plus-square"></i> Add News</a>
                    </li>
                </ul>
              </li>-->
              <li class="treeview {{ setActive('admin/page*') }}"><a href="#"> <i class="fa fa-bookmark"></i> <span>{{{ trans('app.pages') }}}</span>
                <i class="fa fa-angle-left pull-right"></i> </a>
                <ul class="treeview-menu">
                  <li><a href="{{ url(getLang() . '/admin/page') }}"><i class="fa fa-folder"></i> {{{ trans('app.all_pages') }}}</a>
                  </li>
                  <li><a href="{{ url(getLang() . '/admin/page/create') }}"><i class="fa fa-plus-square"></i> {{{ trans('app.add_page') }}}</a>
                  </li>
                </ul>
              </li>
              <!--<li class="treeview {{ setActive(['admin/photo-gallery*', 'admin/video*']) }}"><a href="#"> <i class="fa fa-picture-o"></i> <span>Galleries</span>
                <i class="fa fa-angle-left pull-right"></i> </a>
                <ul class="treeview-menu">
                  <li>
                    <a href="{{ url(getLang() . '/admin/photo-gallery') }}"><i class="fa fa-camera"></i> Photo Galleries</a>
                  </li>
                  <li>
                    <a href="{{ url(getLang() . '/admin/video') }}"><i class="fa fa-play-circle-o"></i> Video Galleries</a>
                  </li>

                </ul>
              </li>-->
              <li class="treeview {{ setActive('admin/article*') }}"><a href="#"> <i class="fa fa-book"></i> <span>{{{ trans('app.articles') }}}</span>
                <i class="fa fa-angle-left pull-right"></i> </a>
                <ul class="treeview-menu">
                  <li>
                    <a href="{{ url(getLang() . '/admin/article/category') }}"><i class="fa fa-sitemap"></i> {{{ trans('app.all_categories') }}}</a>
                  </li>
                  <li><a href="{{ url(getLang() . '/admin/article') }}"><i class="fa fa-archive"></i> {{{ trans('app.all_articles') }}}</a>
                  </li>
                  <li>
                    <a href="{{ url(getLang() . '/admin/article/create') }}"><i class="fa fa-plus-square"></i> {{{ trans('app.add_article') }}}</a>
                  </li>
                </ul>
              </li>
              <li class="treeview {{ setActive('admin/product*') }}"><a href="#"> <i class="fa fa-tags"></i> <span>{{{ trans('app.products') }}}</span>
                <i class="fa fa-angle-left pull-right"></i> </a>
                <ul class="treeview-menu">
                  <li>
                    <a href="{{ url(getLang() . '/admin/product/category') }}"><i class="fa fa-sitemap"></i> {{{ trans('app.all_categories') }}}</a>
                  </li>
                  <li><a href="{{ url(getLang() . '/admin/product') }}"><i class="fa fa-tag"></i> {{{ trans('app.all_products') }}}</a>
                  </li>
                  <li>
                    <a href="{{ url(getLang() . '/admin/product/create') }}"><i class="fa fa-plus-square"></i> {{{ trans('app.add_product') }}}</a>
                  </li>
                </ul>
              </li>
              <!--<li class="treeview {{ setActive('admin/slider*') }}"><a href="#"> <i class="fa fa-tint"></i> <span>Plugins</span>
                <i class="fa fa-angle-left pull-right"></i> </a>
                <ul class="treeview-menu">
                  <li><a href="{{ url(getLang() . '/admin/slider') }}"><i class="fa fa-toggle-up"></i> Sliders</a>
                  </li>
                </ul>
              </li>
              <li class="treeview {{ setActive('admin/project*') }}"><a href="#"> <i class="fa fa-gears"></i> <span>Projects</span>
                <i class="fa fa-angle-left pull-right"></i> </a>
                <ul class="treeview-menu">
                  <li><a href="{{ url(getLang() . '/admin/project') }}"><i class="fa fa-gear"></i> All Projects</a>
                  </li>
                  <li>
                    <a href="{{ url(getLang() . '/admin/project/create') }}"><i class="fa fa-plus-square"></i> Add Project</a>
                  </li>
                </ul>
              </li>
              <li class="treeview {{ setActive('admin/faq*') }}"><a href="#"> <i class="fa fa-question"></i> <span>Faqs</span>
                <i class="fa fa-angle-left pull-right"></i> </a>
                <ul class="treeview-menu">
                  <li><a href="{{ url(getLang() . '/admin/faq') }}"><i class="fa fa-question-circle"></i> All Faq</a></li>
                  <li>
                    <a href="{{ url(getLang() . '/admin/faq/create') }}"><i class="fa fa-plus-square"></i> Add Faq</a>
                  </li>
                </ul>
              </li>-->
              <li class="treeview {{ setActive(['admin/user*', 'admin/group*']) }}"><a href="#"> <i class="fa fa-user"></i> <span>{{{ trans('app.users') }}}</span>
                <i class="fa fa-angle-left pull-right"></i> </a>
                <ul class="treeview-menu">
                  <li><a href="{{ url(getLang() . '/admin/user') }}"><i class="fa fa-user"></i> {{{ trans('app.all_users') }}}</a>
                  </li>
                  <li><a href="{{ url(getLang() . '/admin/group') }}"><i class="fa fa-group"></i> {{{ trans('app.add_group') }}}</a>
                  </li>
                </ul>
              </li>
              <!--<li class="treeview {{ setActive(['admin/log*', 'admin/form-post']) }}"><a href="#"> <i class="fa fa-thumb-tack"></i> <span>Records</span>
                <i class="fa fa-angle-left pull-right"></i> </a>
                <ul class="treeview-menu">
                  <li><a href="{{ url(getLang() . '/admin/log') }}"><i class="fa fa-save"></i> Log</a></li>
                  <li>
                    <a href="{{ url(getLang() . '/admin/form-post') }}"><i class="fa fa-envelope"></i> Form Post</a>
                  </li>
                </ul>
              </li>-->
              <li class="{{ setActive('admin/settings*') }}">
                <a href="{{ url(getLang() . '/admin/settings') }}"> <i class="fa fa-gear"></i> <span>{{{ trans('app.settings') }}}</span> </a>
              </li>
              <li class="{{ setActive('admin/logout*') }}">
                <a href="{{ url(getLang() . '/admin/logout') }}"> <i class="fa fa-sign-out"></i> <span>{{{ trans('app.logout') }}}</span> </a>
              </li>
            </ul>
          </section>
          <!-- /.sidebar -->
        </aside>