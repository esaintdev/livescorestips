@extends('layouts.app')

@section('title', 'Terms & Conditions - ' . config('app.name'))
@section('description', 'Read the terms and conditions of ' . config('app.name') . ' before using our services.')

@section('content')
    <x-category-hero 
        header="Terms & Conditions" 
        :date="now()" 
        tag="h1"
    />

    <div class="blog-container mt-4">
        <article class="bm-card p-4">
            <div class="bm-article-content text-white" style="line-height: 1.8; font-size: 16px;">
                <p>Last updated: {{ date('F d, Y') }}</p>
                        
                        <p>Welcome to {{ config('app.name') }}!</p>

                        <p>These terms and conditions outline the rules and regulations for the use of {{ config('app.name') }}'s Website, located at {{ url('/') }}.</p>

                        <p>By accessing this website we assume you accept these terms and conditions. Do not continue to use {{ config('app.name') }} if you do not agree to take all of the terms and conditions stated on this page.</p>

                        <h3>Cookies</h3>
                        <p>We employ the use of cookies. By accessing {{ config('app.name') }}, you agreed to use cookies in agreement with the {{ config('app.name') }}'s Privacy Policy.</p>

                        <h3>License</h3>
                        <p>Unless otherwise stated, {{ config('app.name') }} and/or its licensors own the intellectual property rights for all material on {{ config('app.name') }}. All intellectual property rights are reserved. You may access this from {{ config('app.name') }} for your own personal use subjected to restrictions set in these terms and conditions.</p>

                        <p>You must not:</p>
                        <ul>
                            <li>Republish material from {{ config('app.name') }}</li>
                            <li>Sell, rent or sub-license material from {{ config('app.name') }}</li>
                            <li>Reproduce, duplicate or copy material from {{ config('app.name') }}</li>
                            <li>Redistribute content from {{ config('app.name') }}</li>
                        </ul>

                        <h3>User Comments</h3>
                        <p>Parts of this website offer an opportunity for users to post and exchange opinions and information in certain areas of the website. {{ config('app.name') }} does not filter, edit, publish or review Comments prior to their presence on the website. Comments do not reflect the views and opinions of {{ config('app.name') }}, its agents and/or affiliates.</p>

                        <h3>Hyperlinking to our Content</h3>
                        <p>The following organizations may link to our Website without prior written approval:</p>
                        <ul>
                            <li>Government agencies;</li>
                            <li>Search engines;</li>
                            <li>News organizations;</li>
                            <li>Online directory distributors may link to our Website in the same manner as they hyperlink to the Websites of other listed businesses.</li>
                        </ul>

                        <h3>iFrames</h3>
                        <p>Without prior approval and written permission, you may not create frames around our Webpages that alter in any way the visual presentation or appearance of our Website.</p>

                        <h3>Content Liability</h3>
                        <p>We shall not be hold responsible for any content that appears on your Website. You agree to protect and defend us against all claims that is rising on your Website.</p>

                        <h3>Reservation of Rights</h3>
                        <p>We reserve the right to request that you remove all links or any particular link to our Website. You approve to immediately remove all links to our Website upon request.</p>

                        <h3>Disclaimer</h3>
                        <p>To the maximum extent permitted by applicable law, we exclude all representations, warranties and conditions relating to our website and the use of this website. Nothing in this disclaimer will:</p>
                        <ul>
                            <li>limit or exclude our or your liability for death or personal injury;</li>
                            <li>limit or exclude our or your liability for fraud or fraudulent misrepresentation;</li>
                            <li>limit any of our or your liabilities in any way that is not permitted under applicable law.</li>
                </ul>
            </div>
        </article>
    </div>
@endsection
