<section class="task7-tools" aria-labelledby="task7-tools-heading">
    <div class="task7-tools-heading">
        <div>
            <p class="eyebrow">LOCAL DISCOVERY</p>
            <h2 id="task7-tools-heading">Explore around you</h2>
        </div>
        <p>Location is requested only when you choose one of these tools.</p>
    </div>
    <div class="task7-tools-grid">
        <section class="task7-tool" aria-labelledby="weather-tool-heading">
            <h3 id="weather-tool-heading">Weather movie picks</h3>
            <p>Use current weather to find a movie genre that fits the day.</p>
            <button type="button" data-task7-action="weather" data-endpoint="{{ route('integrations.weather') }}">Use my location</button>
            <p class="task7-status" data-status="weather" role="status"></p>
            <div class="task7-results" data-results="weather"></div>
        </section>
        <section class="task7-tool" aria-labelledby="nearby-tool-heading">
            <h3 id="nearby-tool-heading">Nearby cinemas</h3>
            <p>Find cinemas and movie-related places near you.</p>
            <button type="button" data-task7-action="nearby" data-endpoint="{{ route('integrations.nearby') }}">Find nearby places</button>
            <p class="task7-status" data-status="nearby" role="status"></p>
            <div class="task7-results" data-results="nearby"></div>
        </section>
    </div>
</section>

<style>
    .task7-tools { display: grid; gap: 16px; padding: 20px; border: 1px solid #2d3541; border-radius: 10px; background: #151b22; }
    .task7-tools-heading { display: flex; justify-content: space-between; align-items: end; gap: 18px; }
    .task7-tools-heading h2 { margin: 0; }
    .task7-tools-heading > p, .task7-tool > p { color: #a8b0bb; }
    .task7-tools-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
    .task7-tool { display: grid; align-content: start; gap: 10px; padding: 16px; border-radius: 8px; background: #191f27; }
    .task7-tool h3, .task7-tool p { margin: 0; }
    .task7-status { min-height: 1.5em; color: #c6ccd4; }
    .task7-result-list { display: grid; gap: 8px; padding-left: 20px; }
    .task7-result-list a { color: #e0ae55; }
    .task7-result-list small { color: #a8b0bb; }
    @media (max-width: 700px) {
        .task7-tools-heading { display: block; }
        .task7-tools-grid { grid-template-columns: 1fr; }
    }
</style>

<script>
(() => {
    const buttons = document.querySelectorAll('[data-task7-action]');

    function renderWeather(container, data) {
        container.replaceChildren();
        const summary = document.createElement('p');
        summary.textContent = (data.weather.description || data.weather.condition) + ' · ' +
            Number(data.weather.temperature_c).toFixed(1) + '°C. Suggested genre: ' + data.suggested_genre + '.';
        container.appendChild(summary);

        if (!data.movies.length) {
            const empty = document.createElement('p');
            empty.textContent = 'No saved movies in this genre yet.';
            container.appendChild(empty);
            return;
        }

        const list = document.createElement('ul');
        list.className = 'task7-result-list';
        data.movies.forEach(movie => {
            const item = document.createElement('li');
            const link = document.createElement('a');
            link.href = movie.url;
            link.textContent = movie.title;
            item.appendChild(link);
            if (movie.year || movie.vote_average) {
                const details = document.createElement('small');
                details.textContent = ' · ' + (movie.year || 'Year unavailable') +
                    ' · ★ ' + Number(movie.vote_average).toFixed(1);
                item.appendChild(details);
            }
            list.appendChild(item);
        });
        container.appendChild(list);
    }

    function renderNearby(container, places) {
        container.replaceChildren();
        if (!places.length) {
            const empty = document.createElement('p');
            empty.textContent = 'No nearby places were found.';
            container.appendChild(empty);
            return;
        }

        const list = document.createElement('ul');
        list.className = 'task7-result-list';
        places.forEach(place => {
            const item = document.createElement('li');
            const name = document.createElement('strong');
            name.textContent = place.name;
            item.appendChild(name);

            const details = document.createElement('small');
            const parts = [];
            if (place.address) parts.push(place.address);
            if (place.distance_km !== null) parts.push(Number(place.distance_km).toFixed(1) + ' km');
            if (parts.length) details.textContent = ' · ' + parts.join(' · ');
            item.appendChild(details);

            if (place.map_url) {
                const link = document.createElement('a');
                link.href = place.map_url;
                link.target = '_blank';
                link.rel = 'noopener noreferrer';
                link.textContent = ' Open map';
                item.appendChild(link);
            }
            list.appendChild(item);
        });
        container.appendChild(list);
    }

    buttons.forEach(button => {
        button.addEventListener('click', () => {
            const kind = button.dataset.task7Action;
            const status = document.querySelector('[data-status="' + kind + '"]');
            const output = document.querySelector('[data-results="' + kind + '"]');

            if (!navigator.geolocation) {
                status.textContent = 'This browser does not provide location access.';
                return;
            }

            button.disabled = true;
            status.textContent = 'Requesting your location…';
            navigator.geolocation.getCurrentPosition(async position => {
                try {
                    const url = new URL(button.dataset.endpoint, window.location.origin);
                    url.searchParams.set('latitude', position.coords.latitude.toFixed(6));
                    url.searchParams.set('longitude', position.coords.longitude.toFixed(6));
                    if (kind === 'nearby') url.searchParams.set('radius_km', '10');

                    const response = await fetch(url, { headers: { Accept: 'application/json' } });
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.message || 'The integration is unavailable.');

                    if (kind === 'weather') {
                        renderWeather(output, data);
                        status.textContent = 'Weather-based movie suggestions are ready.';
                    } else {
                        renderNearby(output, data.places || []);
                        status.textContent = 'Nearby places are ready.';
                    }
                } catch (error) {
                    status.textContent = error.message || 'The integration is unavailable.';
                } finally {
                    button.disabled = false;
                }
            }, error => {
                const messages = {
                    1: 'Location permission was denied.',
                    2: 'Your location is unavailable.',
                    3: 'Location request timed out.'
                };
                status.textContent = messages[error.code] || 'Could not get your location.';
                button.disabled = false;
            }, { enableHighAccuracy: false, timeout: 10000, maximumAge: 300000 });
        });
    });
})();
</script>
