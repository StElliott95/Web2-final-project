<style>
    .auth-card { width: min(100%, 480px); margin: 32px auto; padding: 28px; background: #191f27; border: 1px solid #2d3541; border-radius: 10px; }
    .auth-card h1 { margin: 0; font-size: clamp(27px, 4vw, 34px); }
    .auth-eyebrow { margin: 0 0 7px; color: #e0ae55; font-size: 12px; font-weight: 700; letter-spacing: .12em; }
    .auth-intro { margin: 8px 0 22px; color: #a8b0bb; }
    .auth-form { display: grid; gap: 10px; }
    .auth-form label:not(.auth-checkbox) { margin-top: 5px; font-weight: 700; }
    .auth-form button { width: 100%; margin-top: 10px; }
    .auth-checkbox { display: flex; align-items: center; gap: 9px; margin-top: 6px; color: #a8b0bb; }
    .auth-checkbox input { width: 17px; height: 17px; accent-color: #e0ae55; }
    .auth-links { display: grid; gap: 10px; margin-top: 20px; color: #a8b0bb; font-size: 14px; }
    .auth-links a, .auth-card a { color: #e0ae55; }
    .auth-links span { display: block; }
    .auth-social-separator { display: flex; align-items: center; gap: 12px; margin: 18px 0; color: #a8b0bb; font-size: 13px; }
    .auth-social-separator::before, .auth-social-separator::after { content: ''; height: 1px; flex: 1; background: #2d3541; }
    .auth-social-button { width: 100%; background: #202832; color: #f4f3ef !important; border-color: #3b4654; }
    .auth-help { color: #a8b0bb; font-size: 13px; }
    .auth-section-title { margin: 0 0 14px; font-size: 19px; }
    .auth-divider { border: 0; border-top: 1px solid #2d3541; margin: 24px 0; }
    .auth-card textarea { min-height: 110px; resize: vertical; }
    @media (max-width: 600px) { .auth-card { margin: 16px auto; padding: 21px; } }
</style>
