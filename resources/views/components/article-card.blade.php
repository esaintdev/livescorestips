{{-- Desktop version --}}
<div class="new-card hideSM">
    <div class="col-12">
        <div class="row">
            <div class="px-1 py-1 blog-img-wrap col-lg-12 col-5">
                <img src="{{ $image }}" class="img-fluid blog-img" alt="{{ $titleShort ?? Str::limit($title, 50) }}" style="height:201px;">
                @if(isset($category) && $category)
                    <span class="badge badge-primary"
                        style="position: absolute; top: 10px; left: 15px; background: linear-gradient(135deg, #C1392A 0%, #a02f22 100%); padding: 5px 12px; font-size: 11px;">
                        {{ $category }}
                    </span>
                @endif
            </div>
            <div class="px-1 py-1 col-lg-12 col-7">
                <div class="blog-title-wrap">
                    <a href="{{ $url }}" target="_blank">
                        <p class="hideSM">{{ $title }}</p>
                        <p class="hideLG">{{ $titleShort ?? Str::limit($title, 50) }}</p>
                    </a>
                    <a href="{{ $url }}" target="_blank">
                        <button class="btn blog-btn d-none d-lg-block">Read More »</button>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Mobile version --}}
<div class="new-card hideLG"
    style="box-shadow: 0px 0px 6px 2px grey; padding-top: 7px; -webkit-box-shadow: 0 0 20px rgb(0 0 0 / 10%); box-shadow: 0 0 20px rgb(0 0 0 / 10%); padding-bottom: 0px; padding-left: 4px; padding-bottom: 2px;">
    <div class="row" style="margin: 0px">
        <div class="p-0 blog-img-wrap col-lg-3 col-4" style="position: relative;">
            <a href="{{ $url }}" target="_blank">
                <img src="{{ $image }}" alt="{{ $titleShort ?? Str::limit($title, 50) }}">
            </a>
            @if(isset($category) && $category)
                <span class="badge badge-primary"
                    style="position: absolute; top: 5px; left: 5px; background: linear-gradient(135deg, #C1392A 0%, #a02f22 100%); padding: 3px 8px; font-size: 9px;">
                    {{ $category }}
                </span>
            @endif
        </div>
        <div class="p-0 col-lg-9 col-8">
            <div class="blog-title-wrap" style="padding-top: 0px;">
                <h3>
                    <a href="{{ $url }}" target="_blank">
                        <p class="hideLG">{{ $titleShort ?? Str::limit($title, 50) }}</p>
                        <p class="hideSM">{{ $title }}</p>
                    </a>
                    <p class="text-muted" style="font-size: small!important; color:#7b241c;">{{ $date }}</p>
                </h3>
            </div>
        </div>
    </div>
</div>