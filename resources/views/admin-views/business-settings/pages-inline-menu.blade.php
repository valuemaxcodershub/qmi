<div class="inline-page-menu my-4">
    <ul class="list-unstyled">
        <li class="{{ Request::is('admin/business-settings/terms-condition') ?'active':'' }}"><a href="{{route('admin.business-settings.terms-condition')}}">{{translate('terms_&_Conditions')}}</a></li>
        <li class="{{ Request::is('admin/business-settings/privacy-policy') ?'active':'' }}"><a href="{{route('admin.business-settings.privacy-policy')}}">{{translate('privacy_Policy')}}</a></li>
        <li class="{{ Request::is('admin/business-settings/page/cancellation-refund-return') ?'active':'' }}"><a href="{{route('admin.business-settings.page',['cancellation-refund-return'])}}">Cancellation, Refund & Return Policy</a></li>
        <li class="{{ Request::is('admin/business-settings/page/premium-services') ?'active':'' }}"><a href="{{route('admin.business-settings.page',['premium-services'])}}">Premium Services</a></li>
        <li class="{{ Request::is('admin/business-settings/page/trade-assurance') ?'active':'' }}"><a href="{{route('admin.business-settings.page',['trade-assurance'])}}">Trade Assurance</a></li>
        <li class="{{ Request::is('admin/business-settings/about-us') ?'active':'' }}"><a href="{{route('admin.business-settings.about-us')}}">{{translate('about_Us')}}</a></li>
        <li class="{{ Request::is('admin/helpTopic/list') ?'active':'' }}"><a href="{{route('admin.helpTopic.list')}}">{{translate('FAQ')}}</a></li>
        @if(theme_root_path() == 'theme_fashion')
        <li class="{{ Request::is('admin/business-settings/features-section') ?'active':'' }}"><a href="{{route('admin.business-settings.features-section')}}">{{translate('features_Section')}}</a></li>
        @endif
    </ul>
</div>
