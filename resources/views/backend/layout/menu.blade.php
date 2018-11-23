<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">
  <!-- sidebar: style can be found in sidebar.less -->
  <section class="sidebar">
    <!-- sidebar menu: : style can be found in sidebar.less -->
    <ul class="sidebar-menu">
      @if (Entrust::can(['dashboard']))
      <li class="{{ setActive('admin') }}"><a href="{{ url(getLang() . '/admin/dashboard') }}"> <i class="fa fa-dashboard"></i> <span>{{{ trans('app.dashboard') }}}</span>
      </a></li>
      @endif
      @if (Entrust::can(['read_menu_group']))
      <li class="treeview {{ setActive([ 'admin/menu-group*', 'admin/customize*' ]) }}"><a href="#"> <i class="fa fa-paint-brush"></i> <span>{{{ trans('app.appearance') }}}</span>
        <i class="fa fa-angle-left pull-right"></i> </a>
        <ul class="treeview-menu">
          <li class="{!! setActive([ 'admin/menu-group*' ]) !!}">
            <a href="{{ url(getLang() . '/admin/menu-group') }}"><i class="fa fa-bars"></i> {{{ trans('app.menus') }}}</a>
          </li>
        </ul>
      </li>
      @endif

      @if (Entrust::can(['read_page']))
      <li class="treeview {{ setActive('admin/page*') }}"><a href="#"> <i class="fa fa-bookmark"></i> <span>{{{ trans('app.pages') }}}</span>
        <i class="fa fa-angle-left pull-right"></i> </a>
        <ul class="treeview-menu">
          <li class="{!! setActive([ 'admin/page' ]) !!}"><a href="{{ url(getLang() . '/admin/page') }}"><i class="fa fa-folder"></i> {{{ trans('app.all_pages') }}}</a>
          </li>
          @if (Entrust::can(['create_page']))
          <li class="{!! setActive([ 'admin/page/create' ]) !!}"><a href="{{ url(getLang() . '/admin/page/create') }}"><i class="fa fa-plus-square"></i> {{{ trans('app.add_page') }}}</a>
          </li>
          @endif
        </ul>
      </li>
      @endif

      @if (Entrust::can(['read_article']))
      <li class="treeview {{ setActive([ 'admin/article*', 'admin/taxonomy/article*' ]) }}"><a href="#"> <i class="fa fa-book"></i> <span>{{{ trans('app.articles') }}}</span>
        <i class="fa fa-angle-left pull-right"></i> </a>
        <ul class="treeview-menu">
          
          <li class="{!! setActive([ 'admin/taxonomy/article' ]) !!}">
            <a href="{{ url(getLang() . '/admin/taxonomy/article') }}"><i class="fa fa-sitemap"></i> {{{ trans('app.all_categories') }}}</a>
          </li>
           
          <li class="{!! setActive([ 'admin/article' ]) !!}">
            <a href="{{ url(getLang() . '/admin/article') }}"><i class="fa fa-archive"></i> {{{ trans('app.all_articles') }}}</a>
          </li>

          @if (Entrust::can(['create_article']))
          <li class="{!! setActive([ 'admin/article/create' ]) !!}">
            <a href="{{ url(getLang() . '/admin/article/create') }}"><i class="fa fa-plus-square"></i> {{{ trans('app.add_article') }}}</a>
          </li>
          @endif
        </ul>
      </li>
      @endif

      <!-- Banner -->
      @if (Entrust::can(['read_banner']))
      <li class="treeview {{ setActive([ 'admin/banner*', 'admin/taxonomy/banner*' ]) }}"><a href="#"> <i class="fa fa-picture-o"></i> <span>{{{ trans('app.banners') }}}</span>
        <i class="fa fa-angle-left pull-right"></i> </a>
        <ul class="treeview-menu">
          <li class="{{ setActive(['admin/banner']) }}"><a href="{{ url(getLang() . '/admin/banner') }}"><i class="fa fa-picture-o"></i> {{{ trans('app.all_banners') }}}</a>
          </li>
          @if (Entrust::can(['create_banner']))
          <li class="{{ setActive(['admin/banner/create']) }}">
            <a href="{{ url(getLang() . '/admin/banner/create') }}"><i class="fa fa-plus-square"></i> {{{ trans('app.add_banner') }}}</a>
          </li>
          @endif
        </ul>
      </li>
      @endif
      <!-- end banner -->

      <!-- Products -->
      @if (Entrust::can(['read_product_hotel']) || Entrust::can(['read_product_playground']) || Entrust::can(['read_product_trip']) || Entrust::can(['read_product_merchant']))
      <li class="treeview {{ setActive(['admin/product*', 'admin/taxonomy/product*', 'admin/taxonomy/hotel*', 'admin/taxonomy/playground*', 'admin/taxonomy/trip*']) }}"><a href="#"> <i class="fa fa-tags"></i> <span>{{{ trans('app.products') }}}</span>
        <i class="fa fa-angle-left pull-right"></i> </a>
        <ul class="treeview-menu">
          @if (Entrust::can(['read_product_attribute']))
          <li class="{{ setActive(['admin/product/attribute']) }}">
            <a href="{{ url(getLang() . '/admin/product/attribute') }}"><i class="fa fa-tag"></i> {{{ trans('app.attributes') }}}</a>
          </li>
          @endif

          @if (Entrust::can(['read_product_attribute_option']))
          <li class="{{ setActive(['admin/product/attribute/option']) }}">
            <a href="{{ url(getLang() . '/admin/product/attribute/option') }}"><i class="fa fa-tags"></i> {{{ trans('app.attribute_options') }}}</a>
          </li>
          @endif

          @if (Entrust::can(['upload_bulk_product_attribute_option']))
          <li class="{{ setActive(['admin/product/bulk-upload/product-attribute-option']) }}">
                <a href="{{ url(getLang() . '/admin/product/bulk-upload/product-attribute-option') }}"><i class="fa fa-upload"></i> {{{ trans('app.bulk_upload') }}}</a>
          </li>
          @endif

          <!-- product hotel -->
          @if (Entrust::can(['read_product_hotel']))
          <li class="{{ setActive2('admin/product/create?post_type=hotel', [ 'post_type' ]) }}
          {{ setActive2('admin/product?post_type=hotel', [ 'post_type' ]) }} 
          {{ setActive2('admin/product/bulk-upload/master?post_type=hotel', [ 'post_type' ]) }}
          {{ setActive2('admin/product/bulk-upload/variant?post_type=hotel', [ 'post_type' ]) }}
          {{ setActive2('admin/product/bulk-upload/product-gallery?post_type=hotel', [ 'post_type' ]) }}
          {{ setActive([ 'admin/product/hotel*', 'admin/taxonomy/hotel*', 'admin/product/attribute-posttype*', 'admin/product/filter*']) }}">
            <a href="{{ url(getLang() . '/admin/product/hotel') }}">
              <i class="fa fa-h-square"></i> <span>{{{ trans('app.hotel') }}}</span>
              <i class="fa fa-angle-left pull-right"></i>
            </a>
            <ul class="treeview-menu">
              <li class="{{ setActive('admin/product/attribute-posttype/hotel') }}">
                <a href="{{ url(getLang() . '/admin/product/attribute-posttype/hotel') }}"><i class="fa fa-tag"></i> {{{ trans('app.attributes') }}}</a>
              </li>
              <li class="{{ setActive('admin/taxonomy/hotel*') }}">
                <a href="{{ url(getLang() . '/admin/taxonomy/hotel') }}"><i class="fa fa-map-marker"></i> {{{ trans('app.locations') }}}</a>
              </li>
              <li class="{{ setActive2('admin/product?post_type=hotel', [ 'post_type' ]) }} {{ setActive2( 'admin/product/filter?post_type=hotel', [ 'post_type' ]) }}">
                <a href="{{ url(getLang() . '/admin/product?post_type=hotel') }}"><i class="fa fa-list-ol"></i> {{{ trans('app.all_hotels') }}}</a>
              </li>
              @if (Entrust::can(['create_product_hotel']))
              <li class="{{ setActive2('admin/product/create?post_type=hotel', [ 'post_type' ]) }}">
                <a href="{{ url(getLang() . '/admin/product/create?post_type=hotel') }}"><i class="fa fa-plus-square"></i> {{{ trans('app.add_hotel') }}}</a>
              </li>
              @endif
              <li class="{{ setActive2('admin/product/bulk-upload/master?post_type=hotel', [ 'post_type' ]) }}
              {{ setActive2('admin/product/bulk-upload/variant?post_type=hotel', [ 'post_type' ]) }}
              {{ setActive2('admin/product/bulk-upload/product-gallery?post_type=hotel', [ 'post_type' ]) }}">
                <a href="{{ url(getLang() . '/admin/product/bulk-upload/master?post_type=hotel') }}"><i class="fa fa-upload"></i> {{{ trans('app.bulk_upload') }}}</a>
              </li>
            </ul>
          </li>
          @endif
          <!-- product hotel -->

          <!-- product playground -->
          @if (Entrust::can(['read_product_playground']))
          <li class="{{ setActive2('admin/product/create?post_type=playground', [ 'post_type' ]) }}
          {{ setActive2('admin/product?post_type=playground', [ 'post_type' ]) }} 
          {{ setActive2('admin/product/bulk-upload/master?post_type=playground', [ 'post_type' ]) }}
          {{ setActive2('admin/product/bulk-upload/variant?post_type=playground', [ 'post_type' ]) }}
          {{ setActive2('admin/product/bulk-upload/product-gallery?post_type=playground', [ 'post_type' ]) }}
          {{ setActive([ 'admin/product/playground*', 'admin/taxonomy/playground*', 'admin/product/attribute-posttype*', 'admin/product/filter*']) }}">
            <a href="{{ url(getLang() . '/admin/product/playground') }}">
              <i class="fa fa-child"></i> <span>{{{ trans('app.playground') }}}</span>
              <i class="fa fa-angle-left pull-right"></i>
            </a>
            <ul class="treeview-menu">
              <li class="{{ setActive('admin/product/attribute-posttype/playground') }}">
                <a href="{{ url(getLang() . '/admin/product/attribute-posttype/playground') }}"><i class="fa fa-tag"></i> {{{ trans('app.attributes') }}}</a>
              </li>
              <li class="{{ setActive('admin/taxonomy/playground*') }}">
                <a href="{{ url(getLang() . '/admin/taxonomy/playground') }}"><i class="fa fa-map-marker"></i> {{{ trans('app.locations') }}}</a>
              </li>
              <li class="{{ setActive2('admin/product?post_type=playground', [ 'post_type' ]) }} {{ setActive2( 'admin/product/filter?post_type=playground', [ 'post_type' ]) }}">
                <a href="{{ url(getLang() . '/admin/product?post_type=playground') }}"><i class="fa fa-list-ol"></i> {{{ trans('app.all_playgrounds') }}}</a>
              </li>
              @if (Entrust::can(['create_product_playground']))
              <li class="{{ setActive2('admin/product/create?post_type=playground', [ 'post_type' ]) }}">
                <a href="{{ url(getLang() . '/admin/product/create?post_type=playground') }}"><i class="fa fa-plus-square"></i> {{{ trans('app.add_playground') }}}</a>
              </li>
              @endif
              <li class="{{ setActive2('admin/product/bulk-upload/master?post_type=playground', [ 'post_type' ]) }}
          {{ setActive2('admin/product/bulk-upload/variant?post_type=playground', [ 'post_type' ]) }}
          {{ setActive2('admin/product/bulk-upload/product-gallery?post_type=playground', [ 'post_type' ]) }}">
                <a href="{{ url(getLang() . '/admin/product/bulk-upload/master?post_type=playground') }}"><i class="fa fa-upload"></i> {{{ trans('app.bulk_upload') }}}</a>
              </li>
            </ul>
          </li>
          @endif
          <!-- product playground -->

          <!-- trip -->
          @if (Entrust::can(['read_product_trip']))
          <li class="{{ setActive2('admin/product/create?post_type=trip', [ 'post_type' ]) }}
          {{ setActive2('admin/product?post_type=trip', [ 'post_type' ]) }} 
          {{ setActive2('admin/product/bulk-upload/master?post_type=trip', [ 'post_type' ]) }}
          {{ setActive2('admin/product/bulk-upload/variant?post_type=trip', [ 'post_type' ]) }}
          {{ setActive2('admin/product/bulk-upload/product-gallery?post_type=trip', [ 'post_type' ]) }}
          {{ setActive([ 'admin/product/trip*', 'admin/taxonomy/trip*', 'admin/product/attribute-posttype*', 'admin/product/filter*']) }}">
            <a href="{{ url(getLang() . '/admin/product/trip') }}">
              <i class="fa fa-child"></i> <span>{{{ trans('app.trip') }}}</span>
              <i class="fa fa-angle-left pull-right"></i>
            </a>
            <ul class="treeview-menu">
              <li class="{{ setActive('admin/product/attribute-posttype/trip') }}">
                <a href="{{ url(getLang() . '/admin/product/attribute-posttype/trip') }}"><i class="fa fa-tag"></i> {{{ trans('app.attributes') }}}</a>
              </li>
              <li class="{{ setActive('admin/taxonomy/trip*') }}">
                <a href="{{ url(getLang() . '/admin/taxonomy/trip') }}"><i class="fa fa-map-marker"></i> {{{ trans('app.locations') }}}</a>
              </li>
              <li class="{{ setActive2('admin/product?post_type=trip', [ 'post_type' ]) }} {{ setActive2( 'admin/product/filter?post_type=trip', [ 'post_type' ]) }}">
                <a href="{{ url(getLang() . '/admin/product?post_type=trip') }}"><i class="fa fa-list-ol"></i> {{{ trans('app.all_trips') }}}</a>
              </li>
              @if (Entrust::can(['create_product_trip']))
              <li class="{{ setActive2('admin/product/create?post_type=trip', [ 'post_type' ]) }}">
                <a href="{{ url(getLang() . '/admin/product/create?post_type=trip') }}"><i class="fa fa-plus-square"></i> {{{ trans('app.add_trip') }}}</a>
              </li>
              @endif
            </ul>
          </li>
          @endif
          <!-- end trip -->

          <!-- product merchant -->
          @if (Entrust::can(['read_product_merchant']))
          <li class="{{ setActive2('admin/product/create?post_type=merchant', [ 'post_type' ]) }}
          {{ setActive2('admin/product?post_type=merchant', [ 'post_type' ]) }} 
          {{ setActive2('admin/product/bulk-upload/master?post_type=merchant', [ 'post_type' ]) }}
          {{ setActive2('admin/product/bulk-upload/variant?post_type=merchant', [ 'post_type' ]) }}
          {{ setActive2('admin/product/bulk-upload/product-gallery?post_type=merchant', [ 'post_type' ]) }}
          {{ setActive([ 'admin/product/merchant*', 'admin/taxonomy/merchant*', 'admin/product/attribute-posttype*', 'admin/product/filter*']) }}">
            <a href="{{ url(getLang() . '/admin/product/merchant') }}">
              <i class="fa fa-child"></i> <span>{{{ trans('app.merchant') }}}</span>
              <i class="fa fa-angle-left pull-right"></i>
            </a>
            <ul class="treeview-menu">
              <li class="{{ setActive('admin/product/attribute-posttype/merchant') }}">
                <a href="{{ url(getLang() . '/admin/product/attribute-posttype/merchant') }}"><i class="fa fa-tag"></i> {{{ trans('app.attributes') }}}</a>
              </li>
              <li class="{{ setActive('admin/taxonomy/merchant*') }}">
                <a href="{{ url(getLang() . '/admin/taxonomy/merchant') }}"><i class="fa fa-map-marker"></i> {{{ trans('app.merchant_category') }}}</a>
              </li>
              <li class="{{ setActive2('admin/product?post_type=merchant', [ 'post_type' ]) }} {{ setActive2( 'admin/product/filter?post_type=merchant', [ 'post_type' ]) }}">
                <a href="{{ url(getLang() . '/admin/product?post_type=merchant') }}"><i class="fa fa-list-ol"></i> {{{ trans('app.all_merchant') }}}</a>
              </li>
              @if (Entrust::can(['create_product_merchant']))
              <li class="{{ setActive2('admin/product/create?post_type=merchant', [ 'post_type' ]) }}">
                <a href="{{ url(getLang() . '/admin/product/create?post_type=merchant') }}"><i class="fa fa-plus-square"></i> {{{ trans('app.add_merchant') }}}</a>
              </li>
              @endif
            </ul>
          </li>
          @endif
          <!-- product merchant -->
        </ul>
      </li>
      @endif

      @if (Entrust::can(['read_order']))
      <li class="{{ setActive('admin/order*') }}">
        <a href="{{ url(getLang() . '/admin/order/hotel') }}"> <i class="fa fa-shopping-cart"></i> <span>{{{ trans('app.orders') }}}</span> </a>
      </li>
      @endif

      @if (Entrust::can(['read_payment_confirmation']))
      <li class="{{ setActive('admin/payment-confirmation*') }}">
        <a href="{{ url(getLang() . '/admin/payment-confirmation') }}"> <i class="fa fa-money"></i> <span>{{{ trans('app.payment_confirmation') }}}</span> </a>
      </li>
      @endif

      <!-- CFP -->
      @if(Entrust::can(['read_cfp_client']))
      <li class="treeview {{ setActive(['admin/cfp*']) }}"><a href="#"> <i class="fa fa-black-tie"></i> <span>{{{ trans('app.CFP') }}}</span>
        <i class="fa fa-angle-left pull-right"></i> </a>
        <ul class="treeview-menu">
          @if (Entrust::can(['read_cfp_client']))
            <li class="{{ setActive(['admin/cfp/client']) }}"><a href="{{ url(getLang() . '/admin/cfp/client') }}"><i class="fa fa-sitemap"></i> {{{ trans('app.clients') }}}</a>
          @endif
          @if (Entrust::can(['read_cfp_schedule']))
            <li class="{{ setActive(['admin/cfp/schedule']) }}"><a href="{{ url(getLang() . '/admin/cfp/schedule') }}"><i class="fa fa-calendar"></i> {{{ trans('app.schedules') }}}</a>
          @endif
          @if (Entrust::can(['read_cfp_schedule_dayoff']))
            <li class="{{ setActive(['admin/cfp/schedule_dayoff']) }}"><a href="{{ url(getLang() . '/admin/cfp/schedule_dayoff') }}"><i class="fa fa-calendar"></i> {{{ trans('app.cfp_schedule_dayoff') }}}</a>
          @endif
        </ul>
      </li>
      @endif

      @if(Entrust::can(['read_wallet']))
      <li class="treeview {{ setActive(['admin/wallet*']) }}"><a href="#"> <i class="fa fa-money"></i> <span>{{{ trans('app.wallet') }}}</span>
        <i class="fa fa-angle-left pull-right"></i> </a>
        <ul class="treeview-menu">
          @if (Entrust::can(['read_wallet_category']))
            <li class="{!! setActive([ 'admin/taxonomy/wallet' ]) !!}">
              <a href="{{ url(getLang() . '/admin/taxonomy/wallet') }}"><i class="fa fa-sitemap"></i> {{{ trans('app.all_categories') }}}</a>
            </li>
          @endif
        </ul>
      </li>
      @endif
      @if(Entrust::can(['read_finance']))
      <li class="treeview {{ setActive(['admin/finance*']) }}"><a href="#"> <i class="fa fa-money"></i> <span>{{{ trans('app.finance') }}}</span>
        <i class="fa fa-angle-left pull-right"></i> </a>
        <ul class="treeview-menu">
          @if (Entrust::can(['read_wallet_category']))
            <li class="{!! setActive([ 'admin/taxonomy/wallet' ]) !!}">
              <a href="{{ url(getLang() . '/admin/taxonomy/wallet') }}"><i class="fa fa-sitemap"></i> {{{ trans('app.wallet_categories') }}}</a>
            </li>
          @endif
          @if (Entrust::can(['read_insurance_type']))
            <li class="{!! setActive([ 'admin/taxonomy/insurance_type' ]) !!}">
              <a href="{{ url(getLang() . '/admin/taxonomy/insurance_type') }}"><i class="fa fa-sitemap"></i> {{{ trans('app.insurance_type') }}}</a>
            </li>
          @endif
          @if (Entrust::can(['read_investment_information']))
            <li class="{!! setActive([ 'admin/investment-information' ]) !!}">
              <a href="{{ url(getLang() . '/admin/investment-information') }}"><i class="fa fa-line-chart"></i> {{{ trans('app.investment_information') }}}</a>
            </li>
          @endif
          @if (Entrust::can(['read_financial_health_structure']))
            <li class="{!! setActive([ 'admin/taxonomy/financial_health_structure' ]) !!}">
              <a href="{{ url(getLang() . '/admin/taxonomy/financial_health_structure') }}"><i class="fa fa-sitemap"></i> {{{ trans('app.financial_health_structure') }}}</a>
            </li>
          @endif
        </ul>
      </li>
      @endif

      <!-- Bank -->
      @if (Entrust::can(['read_bank']))
      <li class="{{ setActive('admin/bank*') }}">
        <a href="{{ url(getLang() . '/admin/bank') }}"> <i class="fa fa-money"></i> <span>{{{ trans('app.bank') }}}</span> </a>
      </li>
      @endif
      
      <!-- References -->
      @if (Entrust::can(['read_reference']))
      <li class="{{ setActive('admin/reference*') }}">
        <a href="{{ url(getLang() . '/admin/reference') }}"> <i class="fa fa-address-book"></i> <span>{{{ trans('app.reference') }}}</span> </a>
      </li>
      @endif
      <!-- grades -->
      @if (Entrust::can(['read_grade']))
      <li class="{{ setActive('admin/grade*') }}">
        <a href="{{ url(getLang() . '/admin/grade') }}"> <i class="fa fa-male"></i> <span>{{{ trans('app.grade') }}}</span> </a>
      </li>
      @endif

      <!-- goals -->
      @if (Entrust::can(['read_goal']))
      <li class="{{ setActive('admin/goal*') }}">
        <a href="{{ url(getLang() . '/admin/goal') }}"> <i class="fa fa-bullseye"></i> <span>{{{ trans('app.goal') }}}</span> </a>
      </li>
      @endif
      <!-- end goals -->

      <!-- branch -->
      @if (Entrust::can(['read_branch']))
        <li class="{!! setActive([ 'admin/taxonomy/branch' ]) !!}">
          <a href="{{ url(getLang() . '/admin/taxonomy/branch') }}"><i class="fa fa-map-marker"></i> {{{ trans('app.all_branches') }}}</a>
        </li>
      @endif
      <!-- end branch -->

      @if(Entrust::can(['read_user']) || Entrust::can(['read_role']) || Entrust::can(['read_permission']) || Entrust::can(['update_tour_guide']))
      <li class="treeview {{ setActive(['admin/user*', 'admin/group*']) }}"><a href="#"> <i class="fa fa-user"></i> <span>{{{ trans('app.users') }}}</span>
        <i class="fa fa-angle-left pull-right"></i> </a>
        <ul class="treeview-menu">
        
          @if (Entrust::can(['update_tour_guide']))
          <li class="{{ setActive(['admin/user/'.Auth::user()->id.'/edit/tourguide']) }}"><a href="{{ URL::route('admin.user.edit.tourguide', array(Auth::user()->id)) }}"><i class="fa fa-user"></i> {{{ trans('app.tour_guide') }}}</a>
          @endif

          @if (Entrust::can(['read_user']))
          <li class="{{ setActive(['admin/user']) }}"><a href="{{ url(getLang() . '/admin/user') }}"><i class="fa fa-user"></i> {{{ trans('app.all_users') }}}</a>
          </li>
          @endif

          @if (Entrust::can(['read_role'])) 
          <li class="{{ setActive(['admin/user/role']) }}"><a href="{{ url(getLang() . '/admin/user/role') }}"><i class="fa fa-group"></i> {{{ trans('app.roles') }}}</a>
          </li>
          @endif 

          @if (Entrust::can(['read_permission']))
          <li class="{{ setActive(['admin/user/permission']) }}"><a href="{{ url(getLang() . '/admin/user/permission') }}"><i class="fa fa-user-times"></i> {{{ trans('app.permissions') }}}</a>
          </li>
          @endif
        </ul>
      </li>
      @endif
      @if(Entrust::can(['read_contact']))
      <li class="{{ setActive('admin/contact-us*') }}">
        <a href="{{ url(getLang() . '/admin/contact-us') }}"> <i class="fa fa-headphones"></i> <span>{{{ trans('app.contact_us') }}}</span> </a>
      </li>
      @endif

      @if(Entrust::can(['read_testimonial']))
      <li class="{{ setActive('admin/testimoni*') }}">
        <a href="{{ url(getLang() . '/admin/testimoni') }}"> <i class="fa fa-headphones"></i> <span>{{{ trans('app.testimonial') }}}</span> </a>
      </li>
      @endif



      <!-- Payment MENU -->

      <li class="{{ setActive('admin/payment*') }}">
        <a href="{{ url(getLang() . '/admin/payments') }}"> <i class="fa fa-money"></i> <span>Payment</span> </a>
      </li>

      <!-- END -->



      @if(Entrust::can(['update_general_setting']) || Entrust::can(['update_reading_setting']) || Entrust::can(['update_commerce_setting'])|| Entrust::can(['update_seo_setting']) || Entrust::can(['update_weekend_days_setting']) || Entrust::can(['update_finance_setting']) )
      <li class="treeview {{ setActive(['admin/settings*']) }}"><a href="#"> <i class="fa fa-cog"></i> <span>{{{ trans('app.settings') }}}</span>
        <i class="fa fa-angle-left pull-right"></i> </a>
        <ul class="treeview-menu">
          @if(Entrust::can(['update_general_setting']))
          <li class="{{ setActive(['admin/settings/general']) }}"><a href="{{ url(getLang() . '/admin/settings/general') }}"><i class="fa fa-sitemap"></i> {{{ trans('app.general') }}}</a>
          </li>
          @endif

          @if(Entrust::can(['update_reading_setting']))
          <li class="{{ setActive(['admin/settings/reading']) }}"><a href="{{ url(getLang() . '/admin/settings/reading') }}"><i class="fa fa-eye"></i> {{{ trans('app.reading') }}}</a>
          </li>
          @endif

          @if(Entrust::can(['update_finance_setting']))
          <li class="{{ setActive([ 'admin/settings/finance/*']) }}">
            <a href="{{ url(getLang() . '/admin/settings/finance') }}">
              <i class="fa fa-money"></i> <span>{{{ trans('app.finance') }}}</span>
              <i class="fa fa-angle-left pull-right"></i>
            </a>
            <ul class="treeview-menu">
              @if (Entrust::can(['update_inflation_setting']))
              <li class="{{ setActive('admin/settings/finance/inflation') }}">
                <a href="{{ url(getLang() . '/admin/settings/finance/inflation') }}"><i class="fa fa-line-chart"></i> {{{ trans('app.inflation') }}}</a>
              </li>
              @endif
              @if (Entrust::can(['update_investment_setting']))
              <li class="{{ setActive('admin/settings/finance/investment') }}">
                <a href="{{ url(getLang() . '/admin/settings/finance/investment') }}"><i class="fa fa-bank"></i> {{{ trans('app.investment') }}}</a>
              </li>
              @endif
              @if (Entrust::can(['update_insurance_setting']))
              <li class="{{ setActive('admin/settings/finance/insurance') }}">
                <a href="{{ url(getLang() . '/admin/settings/finance/insurance') }}"><i class="fa fa-life-ring"></i> {{{ trans('app.insurance') }}}</a>
              </li>
              @endif
              @if (Entrust::can(['update_interest_rate_setting']))
              <li class="{{ setActive('admin/settings/finance/interest-rate') }}">
                <a href="{{ url(getLang() . '/admin/settings/finance/interest-rate') }}"><i class="fa fa-percent"></i> {{{ trans('app.interest_rates') }}}</a>
              </li>
              @endif
              @if (Entrust::can(['update_actual_interest_rate_setting']))
              <li class="{{ setActive('admin/settings/finance/actual-interest-rate') }}">
                <a href="{{ url(getLang() . '/admin/settings/finance/actual-interest-rate') }}"><i class="fa fa-percent"></i> {{{ trans('app.actual_interest_rates') }}}</a>
              </li>
              @endif
            </ul>
          </li>
          @endif
        
          @if(Entrust::can(['update_plan_analysis_setting']))
          <li class="{{ setActive([ 'admin/settings/plan-analysis/*']) }}">
            <a href="{{ url(getLang() . '/admin/settings/plan-analysis') }}">
              <i class="fa fa-money"></i> <span>{{{ trans('app.plan_analysis') }}}</span>
              <i class="fa fa-angle-left pull-right"></i>
            </a>
            <ul class="treeview-menu">
              @if (Entrust::can(['read_triangle_mapping']))
              <li class="{{ setActive('admin/settings/plan-analysis/triangle') }}">
                <a href="{{ url(getLang() . '/admin/settings/plan-analysis/triangle') }}"><i class="fa fa-line-chart"></i> {{{ trans('app.triangle_mapping') }}}</a>
              </li>
              @endif
              @if (Entrust::can(['read_triangle_layer']))
              <li class="{{ setActive('admin/settings/plan-analysis/triangle-layer') }}">
                <a href="{{ url(getLang() . '/admin/settings/plan-analysis/triangle-layer') }}"><i class="fa fa-line-chart"></i> {{{ trans('app.triangle_layer') }}}</a>
              </li>
              @endif
              @if (Entrust::can(['read_action_plan_category']))
              <li class="{{ setActive('admin/settings/plan-analysis/action-plan-category') }}">
                <a href="{{ url(getLang() . '/admin/settings/plan-analysis/action-plan-category') }}"><i class="fa fa-line-chart"></i> {{{ trans('app.action_plan_category') }}}</a>
              </li>
              @endif
              @if (Entrust::can(['read_action_plan']))
              <li class="{!! setActive([ 'admin/taxonomy/action_plan' ]) !!}">
                <a href="{{ url(getLang() . '/admin/taxonomy/action_plan') }}"><i class="fa fa-sitemap"></i> {{{ trans('app.action_plan') }}}</a>
              </li>
            @endif
            </ul>
          </li>
          @endif
          
          @if(Entrust::can(['update_cfp_setting']))
          <li class="{{ setActive(['admin/settings/cfp']) }}"><a href="{{ url(getLang() . '/admin/settings/cfp') }}"><i class="fa fa-user-secret"></i> {{{ trans('app.cfp') }}}</a>
          </li>
          @endif
          @if(Entrust::can(['update_subscription_setting']))
          <li class="{{ setActive(['admin/settings/subscription']) }}"><a href="{{ url(getLang() . '/admin/settings/subscription') }}"><i class="fa fa-id-card"></i> {{{ trans('app.subscription') }}}</a>
          </li>
          @endif
          @if(Entrust::can(['update_wallet_setting']))
            <li class="{{ setActive(['admin/settings/wallet']) }}">
              <a href="{{ url(getLang() . '/admin/settings/wallet') }}"><i class="fa fa-money"></i> {{{ trans('app.wallet') }}}</a>
           </li>
          @endif

          @if(Entrust::can(['update_commerce_setting']))
          <li class="{{ setActive(['admin/settings/commerce']) }}">
            <a href="{{ url(getLang() . '/admin/settings/commerce') }}"><i class="fa fa-shopping-cart"></i> {{{ trans('app.commerce') }}}</a>
          </li>
          @endif

          @if(Entrust::can(['update_weekend_days_setting']))
          <li class="{{ setActive(['admin/settings/weekend-days']) }}">
            <a href="{{ url(getLang() . '/admin/settings/weekend-days') }}"><i class="fa fa-calendar"></i> {{{ trans('app.weekend_days') }}}</a>
          </li>
          @endif

          @if(Entrust::can(['update_email_template_setting']))
          <li class="{{ setActive([ 'admin/settings/email*']) }}">
            <a href="{{ url(getLang() . '/admin/settings/email') }}">
              <i class="fa fa-envelope"></i> <span>{{{ trans('app.email') }}}</span>
              <i class="fa fa-angle-left pull-right"></i>
            </a>
            <ul class="treeview-menu">
              <li class="{{ setActive('admin/settings/email-template') }}">
                <a href="{{ url(getLang() . '/admin/settings/email-template') }}"><i class="fa fa-th-large"></i> {{{ trans('app.template') }}}</a>
              </li>
              <li class="{{ setActive('admin/settings/email-mapping') }}">
                <a href="{{ url(getLang() . '/admin/settings/email-mapping') }}"><i class="fa fa-file-text-o"></i> {{{ trans('app.mapping') }}}</a>
              </li>
            </ul>
          </li>
          @endif
          @if(Entrust::can(['update_notification_setting']))
          <li class="{{ setActive(['admin/settings/notification']) }}">
            <a href="{{ url(getLang() . '/admin/settings/notification') }}"><i class="fa fa-bell"></i> {{{ trans('app.notification') }}}</a>
          </li>
          @endif
        </ul>
      </li>
      @endif
      <li class="{{ setActive('admin/logout*') }}">
        <a href="{{ url('/admin/logout') }}"> <i class="fa fa-sign-out"></i> <span>{{{ trans('app.logout') }}}</span> </a>
      </li>
    </ul>
  </section>
  <!-- /.sidebar -->
</aside>