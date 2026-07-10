@extends('layouts.app')

@section('title', 'Privacy Policy - ' . config('app.name'))
@section('description', 'Read the privacy policy of ' . config('app.name') . ' to understand how we handle your data.')

@section('page-header')
    <h1 class="bm-page-title">Privacy Policy</h1>
    <p class="bm-page-subheader">Last updated: {{ date('F d, Y') }}</p>
@endsection

@section('content')
    <div class="bm-card mb-4 bm-content-page">
        <div class="bm-article-content">
            <p>At {{ config('app.name') }}, accessible from {{ url('/') }}, one of our main priorities is the privacy of our visitors. This Privacy Policy document contains types of information that is collected and recorded by {{ config('app.name') }} and how we use it.</p>

            <h2>Log Files</h2>
            <p>{{ config('app.name') }} follows a standard procedure of using log files. These files log visitors when they visit websites. All hosting companies do this and a part of hosting services' analytics. The information collected by log files include internet protocol (IP) addresses, browser type, Internet Service Provider (ISP), date and time stamp, referring/exit pages, and possibly the number of clicks.</p>

            <h2>Cookies and Web Beacons</h2>
            <p>Like any other website, {{ config('app.name') }} uses 'cookies'. These cookies are used to store information including visitors' preferences, and the pages on the website that the visitor accessed or visited. The information is used to optimize the users' experience by customizing our web page content based on visitors' browser type and/or other information.</p>

            <h2>Google DoubleClick DART Cookie</h2>
            <p>Google is one of a third-party vendor on our site. It also uses cookies, known as DART cookies, to serve ads to our site visitors based upon their visit to www.website.com and other sites on the internet.</p>

            <h2>Privacy Policies</h2>
            <p>You may consult this list to find the Privacy Policy for each of the advertising partners of {{ config('app.name') }}.</p>
            <p>Third-party ad servers or ad networks uses technologies like cookies, JavaScript, or Web Beacons that are used in their respective advertisements and links that appear on {{ config('app.name') }}, which are sent directly to users' browser. They automatically receive your IP address when this occurs. These technologies are used to measure the effectiveness of their advertising campaigns and/or to personalize the advertising content that you see on websites that you visit.</p>

            <h2>Third Party Privacy Policies</h2>
            <p>{{ config('app.name') }}'s Privacy Policy does not apply to other advertisers or websites. Thus, we are advising you to consult the respective Privacy Policies of these third-party ad servers for more detailed information. It may include their practices and instructions about how to opt-out of certain options.</p>

            <h2>Children's Information</h2>
            <p>Another part of our priority is adding protection for children while using the internet. We encourage parents and guardians to observe, participate in, and/or monitor and guide their online activity.</p>
            <p>{{ config('app.name') }} does not knowingly collect any Personal Identifiable Information from children under the age of 13. If you think that your child provided this kind of information on our website, we strongly encourage you to contact us immediately and we will do our best efforts to promptly remove such information from our records.</p>

            <h2>Online Privacy Policy Only</h2>
            <p>This Privacy Policy applies only to our online activities and is valid for visitors to our website with regards to the information that they shared and/or collect in {{ config('app.name') }}. This policy is not applicable to any information collected offline or via channels other than this website.</p>

            <h2>Consent</h2>
            <p>By using our website, you hereby consent to our Privacy Policy and agree to its Terms and Conditions.</p>
        </div>
    </div>
@endsection
