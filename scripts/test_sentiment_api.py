import unittest

from sentiment_api import classify_text


class SentimentApiTest(unittest.TestCase):
    def test_classifies_positive_review(self):
        result = classify_text("I absolutely loved this movie. It was fantastic!")
        self.assertEqual("positive", result["label"])
        self.assertGreaterEqual(result["score"], 0)
        self.assertLessEqual(result["score"], 1)

    def test_classifies_negative_review(self):
        result = classify_text("I hated this movie. It was awful and boring.")
        self.assertEqual("negative", result["label"])
        self.assertGreaterEqual(result["score"], 0)
        self.assertLessEqual(result["score"], 1)

    def test_classifies_neutral_review(self):
        result = classify_text("The movie is 90 minutes long and has two main characters.")
        self.assertEqual("neutral", result["label"])
        self.assertGreaterEqual(result["score"], 0)
        self.assertLessEqual(result["score"], 1)

    def test_rejects_empty_text(self):
        with self.assertRaises(ValueError):
            classify_text("   ")


if __name__ == "__main__":
    unittest.main()
