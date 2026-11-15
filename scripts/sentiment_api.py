#!/usr/bin/env python3
"""Loopback-only sentiment API backed by the open-source VADER lexicon."""

from __future__ import annotations

import json
import os
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from typing import Any

from vaderSentiment.vaderSentiment import SentimentIntensityAnalyzer

ANALYZER = SentimentIntensityAnalyzer()
MAX_BODY_BYTES = 16_384


def classify_text(text: str) -> dict[str, Any]:
    """Return a VADER sentiment label and the matching class share."""
    if not isinstance(text, str) or not text.strip():
        raise ValueError("Review text is required.")

    scores = ANALYZER.polarity_scores(text)
    compound = scores["compound"]

    if compound >= 0.05:
        label, score_key = "positive", "pos"
    elif compound <= -0.05:
        label, score_key = "negative", "neg"
    else:
        label, score_key = "neutral", "neu"

    return {"label": label, "score": round(float(scores[score_key]), 4)}


class SentimentHandler(BaseHTTPRequestHandler):
    server_version = "LocalSentimentAPI/1.0"

    def do_GET(self) -> None:
        if self.path == "/health":
            self._send_json(200, {"status": "ok"})
            return
        self._send_json(404, {"error": "Not found."})

    def do_POST(self) -> None:
        if self.path != "/analyze":
            self._send_json(404, {"error": "Not found."})
            return

        try:
            length = int(self.headers.get("Content-Length", "0"))
            if length <= 0:
                raise ValueError("A JSON request body is required.")
            if length > MAX_BODY_BYTES:
                self._send_json(413, {"error": "Review text is too long."})
                return

            payload = json.loads(self.rfile.read(length).decode("utf-8"))
            result = classify_text(payload.get("inputs") if isinstance(payload, dict) else None)
        except (UnicodeDecodeError, json.JSONDecodeError, ValueError) as error:
            self._send_json(400, {"error": str(error) or "Invalid JSON request."})
            return

        self._send_json(200, result)

    def _send_json(self, status: int, payload: dict[str, Any]) -> None:
        body = json.dumps(payload).encode("utf-8")
        self.send_response(status)
        self.send_header("Content-Type", "application/json; charset=utf-8")
        self.send_header("Content-Length", str(len(body)))
        self.send_header("Cache-Control", "no-store")
        self.end_headers()
        self.wfile.write(body)


def main() -> None:
    host = "127.0.0.1"
    port = int(os.environ.get("SENTIMENT_LOCAL_PORT", "8765"))
    server = ThreadingHTTPServer((host, port), SentimentHandler)
    print(f"Local VADER sentiment API listening at http://{host}:{port}")
    print("Press Ctrl+C to stop it.")
    try:
        server.serve_forever()
    except KeyboardInterrupt:
        print("\nStopping local sentiment API.")
    finally:
        server.server_close()


if __name__ == "__main__":
    main()
