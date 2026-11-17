@props(['label', 'value', 'tone' => 'primary', 'icon', 'subtitle'])
<article class="metric-card metric-{{ $tone }}">
    <div class="metric-top"><span class="metric-label">{{ $label }}</span><span class="metric-icon"><x-icon :name="$icon" /></span></div>
    <div class="metric-number">{{ $value }}</div>
    <div class="metric-subtitle">{{ $subtitle }}</div>
</article>
