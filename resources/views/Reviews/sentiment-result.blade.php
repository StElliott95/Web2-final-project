@if($sentimentReview->sentiment_label)
    <span class="sentiment-{{ $sentimentReview->sentiment_label }}">{{ ucfirst($sentimentReview->sentiment_label) }}</span>
    @if($sentimentReview->sentiment_score !== null)
        <small>(model score {{ number_format((float) $sentimentReview->sentiment_score * 100, 1) }}%)</small>
    @endif
@else
    <span class="sentiment-pending">Not analyzed</span>
@endif
