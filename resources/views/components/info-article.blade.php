@if( (isset($metadata['footer_content']) && !empty($metadata['footer_content'])) || (isset($metadata['header_content']) && !empty($metadata['header_content'])) )
    <div class="bm-card bm-info-article-dynamic mt-4 mb-5">
        <div class="bm-article-content">
            @if(isset($metadata['header_content']) && !empty($metadata['header_content']))
                <h2 class="bm-content-h2 mb-3">{{ $metadata['header_content'] }}</h2>
            @endif
            
            @if(isset($metadata['footer_content']) && !empty($metadata['footer_content']))
                {!! $metadata['footer_content'] !!}
            @endif
        </div>
    </div>
@endif
