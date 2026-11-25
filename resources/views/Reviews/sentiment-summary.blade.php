{{--
   Audience sentiment of a movie (Task 4 / Task 5).
   $summary = ['positive' => 3, 'neutral' => 0, 'negative' => 1, 'total' => 4] or null
   $compact = true for a small one-line version (watchlist rows)
--}}
@php
   $summary = $summary ?? null;
   $compact = $compact ?? false;
   $total = $summary['total'] ?? 0;
   $percent = fn ($count) => $total ? (int) round($count * 100 / $total) : 0;
@endphp
@if($compact)
   @if($total > 0)
<span class="sentiment-mini">
<span class="sentiment-positive">{{ $percent($summary['positive']) }}% positive</span>
<span aria-hidden="true">·</span>
<span class="sentiment-negative">{{ $percent($summary['negative']) }}% negative</span>
<small class="sentiment-muted">({{ $total }})</small>
</span>
   @else
<span class="sentiment-muted">No analyzed reviews</span>
   @endif
@else
<section class="sentiment-summary" aria-labelledby="sentiment-summary-heading">
<h2 id="sentiment-summary-heading">Audience sentiment</h2>
       @if($total > 0)
<div class="sentiment-bar" role="img"
                aria-label="{{ $percent($summary['positive']) }}% positive, {{ $percent($summary['neutral']) }}% neutral, {{ $percent($summary['negative']) }}% negative">
               @if($summary['positive'])<span class="bar-positive" style="flex: {{ $summary['positive'] }}"></span>@endif
               @if($summary['neutral'])<span class="bar-neutral" style="flex: {{ $summary['neutral'] }}"></span>@endif
               @if($summary['negative'])<span class="bar-negative" style="flex: {{ $summary['negative'] }}"></span>@endif
</div>
<p class="sentiment-legend">
<span class="sentiment-positive">● Positive {{ $percent($summary['positive']) }}%</span>
               @if($summary['neutral'])<span class="sentiment-neutral">● Neutral {{ $percent($summary['neutral']) }}%</span>@endif
<span class="sentiment-negative">● Negative {{ $percent($summary['negative']) }}%</span>
<span class="sentiment-muted">Based on {{ $total }} analyzed {{ \Illuminate\Support\Str::plural('review', $total) }}</span>
</p>
       @else
<p class="sentiment-muted">No analyzed reviews yet. The summary appears after reviewers analyze the tone of their reviews.</p>
       @endif
</section>
@endif
@once
<style>
       .sentiment-positive { color: var(--success); }
       .sentiment-negative { color: #ffaaaa; }
       .sentiment-neutral, .sentiment-muted { color: var(--muted); }
       .sentiment-mini { white-space: nowrap; font-size: 14px; }
       .sentiment-summary { background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding: 16px; margin: 0 0 24px; }
       .sentiment-summary h2 { font-size: 17px; margin: 0 0 12px; }
       .sentiment-bar { display: flex; height: 10px; border-radius: 99px; overflow: hidden; background: var(--surface-raised); gap: 2px; }
       .bar-positive { background: var(--success); }
       .bar-neutral { background: var(--muted); }
       .bar-negative { background: var(--danger); }
       .sentiment-legend { display: flex; flex-wrap: wrap; gap: 8px 16px; margin: 10px 0 0; font-size: 14px; }
       .sentiment-legend .sentiment-muted { margin-left: auto; }
</style>
@endonce
