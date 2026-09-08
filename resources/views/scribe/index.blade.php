<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Visual Board & Inventory API Documentation</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.style.css") }}" media="screen">
    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.print.css") }}" media="print">

    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.10/lodash.min.js"></script>

    <link rel="stylesheet"
          href="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/styles/obsidian.min.css">
    <script src="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/highlight.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jets/0.14.1/jets.min.js"></script>

    <style id="language-style">
        /* starts out as display none and is replaced with js later  */
                    body .content .bash-example code { display: none; }
                    body .content .javascript-example code { display: none; }
            </style>

    <script>
        var tryItOutBaseUrl = "http://localhost:8000";
        var useCsrf = Boolean();
        var csrfUrl = "/sanctum/csrf-cookie";
    </script>
    <script src="{{ asset("/vendor/scribe/js/tryitout-5.11.0.js") }}"></script>

    <script src="{{ asset("/vendor/scribe/js/theme-default-5.11.0.js") }}"></script>

</head>

<body data-languages="[&quot;bash&quot;,&quot;javascript&quot;]">

<a href="#" id="nav-button">
    <span>
        MENU
        <img src="{{ asset("/vendor/scribe/images/navbar.png") }}" alt="navbar-image"/>
    </span>
</a>
<div class="tocify-wrapper">
    
            <div class="lang-selector">
                                            <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                            <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                    </div>
    
    <div class="search">
        <input type="text" class="search" id="input-search" placeholder="Search">
    </div>

    <div id="toc">
                    <ul id="tocify-header-introduction" class="tocify-header">
                <li class="tocify-item level-1" data-unique="introduction">
                    <a href="#introduction">Introduction</a>
                </li>
                            </ul>
                    <ul id="tocify-header-authenticating-requests" class="tocify-header">
                <li class="tocify-item level-1" data-unique="authenticating-requests">
                    <a href="#authenticating-requests">Authenticating requests</a>
                </li>
                            </ul>
                    <ul id="tocify-header-visual-board-domain" class="tocify-header">
                <li class="tocify-item level-1" data-unique="visual-board-domain">
                    <a href="#visual-board-domain">Visual Board Domain</a>
                </li>
                                    <ul id="tocify-subheader-visual-board-domain" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="visual-board-domain-kiosk-dashboard-endpoint-api-agregasi-data-untuk-konsumsi-read-only-pada-layar-tv-kiosk-modul-13">
                                <a href="#visual-board-domain-kiosk-dashboard-endpoint-api-agregasi-data-untuk-konsumsi-read-only-pada-layar-tv-kiosk-modul-13">Kiosk Dashboard

Endpoint API agregasi data untuk konsumsi Read-Only pada layar TV Kiosk (Modul 1.3).</a>
                            </li>
                                                            <ul id="tocify-subheader-visual-board-domain-kiosk-dashboard-endpoint-api-agregasi-data-untuk-konsumsi-read-only-pada-layar-tv-kiosk-modul-13" class="tocify-subheader">
                                                                            <li class="tocify-item level-3" data-unique="visual-board-domain-GETapi-v1-visual-board-kiosk">
                                            <a href="#visual-board-domain-GETapi-v1-visual-board-kiosk">Data Kiosk Dashboard</a>
                                        </li>
                                                                    </ul>
                                                                                <li class="tocify-item level-2" data-unique="visual-board-domain-manajemen-jadwal-5r-endpoint-untuk-mengelola-matriks-jadwal-harian-5r-modul-11">
                                <a href="#visual-board-domain-manajemen-jadwal-5r-endpoint-untuk-mengelola-matriks-jadwal-harian-5r-modul-11">Manajemen Jadwal 5R

Endpoint untuk mengelola matriks jadwal harian 5R (Modul 1.1).</a>
                            </li>
                                                            <ul id="tocify-subheader-visual-board-domain-manajemen-jadwal-5r-endpoint-untuk-mengelola-matriks-jadwal-harian-5r-modul-11" class="tocify-subheader">
                                                                            <li class="tocify-item level-3" data-unique="visual-board-domain-GETapi-v1-visual-board-schedules--id-">
                                            <a href="#visual-board-domain-GETapi-v1-visual-board-schedules--id-">Detail Jadwal Bulanan</a>
                                        </li>
                                                                            <li class="tocify-item level-3" data-unique="visual-board-domain-PATCHapi-v1-visual-board-schedule-records--recordId--update-day">
                                            <a href="#visual-board-domain-PATCHapi-v1-visual-board-schedule-records--recordId--update-day">Update Status Harian</a>
                                        </li>
                                                                    </ul>
                                                                                <li class="tocify-item level-2" data-unique="visual-board-domain-manajemen-abnormality-endpoint-untuk-mencatat-dan-mengelola-masalah-abnormality-dari-inspeksi-5r-harian">
                                <a href="#visual-board-domain-manajemen-abnormality-endpoint-untuk-mencatat-dan-mengelola-masalah-abnormality-dari-inspeksi-5r-harian">Manajemen Abnormality

Endpoint untuk mencatat dan mengelola masalah (abnormality) dari inspeksi 5R harian.</a>
                            </li>
                                                            <ul id="tocify-subheader-visual-board-domain-manajemen-abnormality-endpoint-untuk-mencatat-dan-mengelola-masalah-abnormality-dari-inspeksi-5r-harian" class="tocify-subheader">
                                                                            <li class="tocify-item level-3" data-unique="visual-board-domain-GETapi-v1-visual-board-abnormalities">
                                            <a href="#visual-board-domain-GETapi-v1-visual-board-abnormalities">Display a listing of the abnormalities.</a>
                                        </li>
                                                                            <li class="tocify-item level-3" data-unique="visual-board-domain-POSTapi-v1-visual-board-abnormalities">
                                            <a href="#visual-board-domain-POSTapi-v1-visual-board-abnormalities">Buat Abnormality Baru</a>
                                        </li>
                                                                            <li class="tocify-item level-3" data-unique="visual-board-domain-GETapi-v1-visual-board-abnormalities--id-">
                                            <a href="#visual-board-domain-GETapi-v1-visual-board-abnormalities--id-">Detail Abnormality</a>
                                        </li>
                                                                            <li class="tocify-item level-3" data-unique="visual-board-domain-DELETEapi-v1-visual-board-abnormalities--id-">
                                            <a href="#visual-board-domain-DELETEapi-v1-visual-board-abnormalities--id-">Hapus Abnormality</a>
                                        </li>
                                                                            <li class="tocify-item level-3" data-unique="visual-board-domain-PATCHapi-v1-visual-board-abnormalities--id--progress">
                                            <a href="#visual-board-domain-PATCHapi-v1-visual-board-abnormalities--id--progress">Update Progress Abnormality</a>
                                        </li>
                                                                    </ul>
                                                                        </ul>
                            </ul>
            </div>

    <ul class="toc-footer" id="toc-footer">
                    <li style="padding-bottom: 5px;"><a href="{{ route("scribe.postman") }}">View Postman collection</a></li>
                            <li style="padding-bottom: 5px;"><a href="{{ route("scribe.openapi") }}">View OpenAPI spec</a></li>
                <li><a href="http://github.com/knuckleswtf/scribe">Documentation powered by Scribe ✍</a></li>
    </ul>

    <ul class="toc-footer" id="last-updated">
        <li>Last updated: September 8, 2026</li>
    </ul>
</div>

<div class="page-wrapper">
    <div class="dark-box"></div>
    <div class="content">
        <h1 id="introduction">Introduction</h1>
<p>Dokumentasi interaktif untuk Visual Board &amp; Inventory System PT Inalum</p>
<aside>
    <strong>Base URL</strong>: <code>http://localhost:8000</code>
</aside>
<pre><code>This documentation aims to provide all the information you need to work with our API.

&lt;aside&gt;As you scroll, you'll see code examples for working with the API in different programming languages in the dark area to the right (or as part of the content on mobile).
You can switch the language used with the tabs at the top right (or from the nav menu at the top left on mobile).&lt;/aside&gt;</code></pre>

        <h1 id="authenticating-requests">Authenticating requests</h1>
<p>This API is not authenticated.</p>

        <h1 id="visual-board-domain">Visual Board Domain</h1>

    

                        <h2 id="visual-board-domain-kiosk-dashboard-endpoint-api-agregasi-data-untuk-konsumsi-read-only-pada-layar-tv-kiosk-modul-13">Kiosk Dashboard

Endpoint API agregasi data untuk konsumsi Read-Only pada layar TV Kiosk (Modul 1.3).</h2>
                                                    <h2 id="visual-board-domain-GETapi-v1-visual-board-kiosk">Data Kiosk Dashboard</h2>

<p>
</p>

<p>Endpoint ini mengambil agregasi struktur organisasi, tren masalah bulanan,
dan daftar masalah yang belum terselesaikan. Hasil dari kueri ini di-cache
secara otomatis selama 1 menit.</p>

<span id="example-requests-GETapi-v1-visual-board-kiosk">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/visual-board/kiosk" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/visual-board/kiosk"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-visual-board-kiosk">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;success&quot;: true,
    &quot;message&quot;: &quot;Berhasil memuat data Kiosk Dashboard.&quot;,
    &quot;data&quot;: {
        &quot;organization_structure&quot;: [],
        &quot;abnormality_trend&quot;: {
            &quot;month&quot;: &quot;September 2026&quot;,
            &quot;summary&quot;: {
                &quot;open&quot;: 0,
                &quot;in_progress&quot;: 0,
                &quot;resolved&quot;: 0
            }
        },
        &quot;open_problems&quot;: []
    },
    &quot;meta&quot;: []
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-visual-board-kiosk" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-visual-board-kiosk"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-visual-board-kiosk"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-visual-board-kiosk" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-visual-board-kiosk">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-visual-board-kiosk" data-method="GET"
      data-path="api/v1/visual-board/kiosk"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-visual-board-kiosk', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-visual-board-kiosk"
                    onclick="tryItOut('GETapi-v1-visual-board-kiosk');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-visual-board-kiosk"
                    onclick="cancelTryOut('GETapi-v1-visual-board-kiosk');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-visual-board-kiosk"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/visual-board/kiosk</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-visual-board-kiosk"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-visual-board-kiosk"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                                <h2 id="visual-board-domain-manajemen-jadwal-5r-endpoint-untuk-mengelola-matriks-jadwal-harian-5r-modul-11">Manajemen Jadwal 5R

Endpoint untuk mengelola matriks jadwal harian 5R (Modul 1.1).</h2>
                                                    <h2 id="visual-board-domain-GETapi-v1-visual-board-schedules--id-">Detail Jadwal Bulanan</h2>

<p>
</p>

<p>Mengambil detail jadwal bulanan beserta seluruh record hariannya.</p>

<span id="example-requests-GETapi-v1-visual-board-schedules--id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/visual-board/schedules/architecto" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/visual-board/schedules/architecto"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-visual-board-schedules--id-">
            <blockquote>
            <p>Example response (500):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Server Error&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-visual-board-schedules--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-visual-board-schedules--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-visual-board-schedules--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-visual-board-schedules--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-visual-board-schedules--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-visual-board-schedules--id-" data-method="GET"
      data-path="api/v1/visual-board/schedules/{id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-visual-board-schedules--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-visual-board-schedules--id-"
                    onclick="tryItOut('GETapi-v1-visual-board-schedules--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-visual-board-schedules--id-"
                    onclick="cancelTryOut('GETapi-v1-visual-board-schedules--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-visual-board-schedules--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/visual-board/schedules/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-visual-board-schedules--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-visual-board-schedules--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id"                data-endpoint="GETapi-v1-visual-board-schedules--id-"
               value="architecto"
               data-component="url">
    <br>
<p>ULID dari jadwal bulanan. Example: <code>architecto</code></p>
            </div>
                    </form>

                    <h2 id="visual-board-domain-PATCHapi-v1-visual-board-schedule-records--recordId--update-day">Update Status Harian</h2>

<p>
</p>

<p>Memperbarui simbol/status 5R pada hari tertentu dalam satu record jadwal.</p>

<span id="example-requests-PATCHapi-v1-visual-board-schedule-records--recordId--update-day">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PATCH \
    "http://localhost:8000/api/v1/visual-board/schedule-records/architecto/update-day" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"day\": 1,
    \"status\": \"libur\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/visual-board/schedule-records/architecto/update-day"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "day": 1,
    "status": "libur"
};

fetch(url, {
    method: "PATCH",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PATCHapi-v1-visual-board-schedule-records--recordId--update-day">
</span>
<span id="execution-results-PATCHapi-v1-visual-board-schedule-records--recordId--update-day" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PATCHapi-v1-visual-board-schedule-records--recordId--update-day"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PATCHapi-v1-visual-board-schedule-records--recordId--update-day"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PATCHapi-v1-visual-board-schedule-records--recordId--update-day" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PATCHapi-v1-visual-board-schedule-records--recordId--update-day">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PATCHapi-v1-visual-board-schedule-records--recordId--update-day" data-method="PATCH"
      data-path="api/v1/visual-board/schedule-records/{recordId}/update-day"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PATCHapi-v1-visual-board-schedule-records--recordId--update-day', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PATCHapi-v1-visual-board-schedule-records--recordId--update-day"
                    onclick="tryItOut('PATCHapi-v1-visual-board-schedule-records--recordId--update-day');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PATCHapi-v1-visual-board-schedule-records--recordId--update-day"
                    onclick="cancelTryOut('PATCHapi-v1-visual-board-schedule-records--recordId--update-day');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PATCHapi-v1-visual-board-schedule-records--recordId--update-day"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/v1/visual-board/schedule-records/{recordId}/update-day</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PATCHapi-v1-visual-board-schedule-records--recordId--update-day"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PATCHapi-v1-visual-board-schedule-records--recordId--update-day"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>recordId</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="recordId"                data-endpoint="PATCHapi-v1-visual-board-schedule-records--recordId--update-day"
               value="architecto"
               data-component="url">
    <br>
<p>ULID dari record jadwal spesifik. Example: <code>architecto</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>day</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="day"                data-endpoint="PATCHapi-v1-visual-board-schedule-records--recordId--update-day"
               value="1"
               data-component="body">
    <br>
<p>Must be at least 1. Must not be greater than 31. Example: <code>1</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="status"                data-endpoint="PATCHapi-v1-visual-board-schedule-records--recordId--update-day"
               value="libur"
               data-component="body">
    <br>
<p>Example: <code>libur</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>rencana</code></li> <li><code>ok</code></li> <li><code>ok_5r</code></li> <li><code>abnormal</code></li> <li><code>libur</code></li></ul>
        </div>
        </form>

                                <h2 id="visual-board-domain-manajemen-abnormality-endpoint-untuk-mencatat-dan-mengelola-masalah-abnormality-dari-inspeksi-5r-harian">Manajemen Abnormality

Endpoint untuk mencatat dan mengelola masalah (abnormality) dari inspeksi 5R harian.</h2>
                                                    <h2 id="visual-board-domain-GETapi-v1-visual-board-abnormalities">Display a listing of the abnormalities.</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-visual-board-abnormalities">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/visual-board/abnormalities" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/visual-board/abnormalities"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-visual-board-abnormalities">
            <blockquote>
            <p>Example response (500):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Server Error&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-visual-board-abnormalities" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-visual-board-abnormalities"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-visual-board-abnormalities"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-visual-board-abnormalities" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-visual-board-abnormalities">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-visual-board-abnormalities" data-method="GET"
      data-path="api/v1/visual-board/abnormalities"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-visual-board-abnormalities', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-visual-board-abnormalities"
                    onclick="tryItOut('GETapi-v1-visual-board-abnormalities');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-visual-board-abnormalities"
                    onclick="cancelTryOut('GETapi-v1-visual-board-abnormalities');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-visual-board-abnormalities"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/visual-board/abnormalities</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-visual-board-abnormalities"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-visual-board-abnormalities"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="visual-board-domain-POSTapi-v1-visual-board-abnormalities">Buat Abnormality Baru</h2>

<p>
</p>

<p>Endpoint ini mencatat masalah baru yang ditemukan di lapangan.</p>

<span id="example-requests-POSTapi-v1-visual-board-abnormalities">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/visual-board/abnormalities" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"zone_id\": \"architecto\",
    \"date_found\": \"2026-09-08T06:58:41\",
    \"problem_description\": \"n\",
    \"countermeasure_plan\": \"g\",
    \"is_kaizen\": false
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/visual-board/abnormalities"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "zone_id": "architecto",
    "date_found": "2026-09-08T06:58:41",
    "problem_description": "n",
    "countermeasure_plan": "g",
    "is_kaizen": false
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-visual-board-abnormalities">
</span>
<span id="execution-results-POSTapi-v1-visual-board-abnormalities" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-visual-board-abnormalities"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-visual-board-abnormalities"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-visual-board-abnormalities" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-visual-board-abnormalities">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-visual-board-abnormalities" data-method="POST"
      data-path="api/v1/visual-board/abnormalities"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-visual-board-abnormalities', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-visual-board-abnormalities"
                    onclick="tryItOut('POSTapi-v1-visual-board-abnormalities');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-visual-board-abnormalities"
                    onclick="cancelTryOut('POSTapi-v1-visual-board-abnormalities');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-visual-board-abnormalities"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/visual-board/abnormalities</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-visual-board-abnormalities"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-visual-board-abnormalities"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>zone_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="zone_id"                data-endpoint="POSTapi-v1-visual-board-abnormalities"
               value="architecto"
               data-component="body">
    <br>
<p>Must match an existing stored value. Example: <code>architecto</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>monthly_schedule_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="monthly_schedule_id"                data-endpoint="POSTapi-v1-visual-board-abnormalities"
               value=""
               data-component="body">
    <br>
<p>Must match an existing stored value.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>inspection_criteria_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="inspection_criteria_id"                data-endpoint="POSTapi-v1-visual-board-abnormalities"
               value=""
               data-component="body">
    <br>
<p>Must match an existing stored value.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>date_found</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="date_found"                data-endpoint="POSTapi-v1-visual-board-abnormalities"
               value="2026-09-08T06:58:41"
               data-component="body">
    <br>
<p>Must be a valid date. Example: <code>2026-09-08T06:58:41</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>problem_description</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="problem_description"                data-endpoint="POSTapi-v1-visual-board-abnormalities"
               value="n"
               data-component="body">
    <br>
<p>Must not be greater than 1000 characters. Example: <code>n</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>countermeasure_plan</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="countermeasure_plan"                data-endpoint="POSTapi-v1-visual-board-abnormalities"
               value="g"
               data-component="body">
    <br>
<p>Must not be greater than 1000 characters. Example: <code>g</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>is_kaizen</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <label data-endpoint="POSTapi-v1-visual-board-abnormalities" style="display: none">
            <input type="radio" name="is_kaizen"
                   value="true"
                   data-endpoint="POSTapi-v1-visual-board-abnormalities"
                   data-component="body"             >
            <code>true</code>
        </label>
        <label data-endpoint="POSTapi-v1-visual-board-abnormalities" style="display: none">
            <input type="radio" name="is_kaizen"
                   value="false"
                   data-endpoint="POSTapi-v1-visual-board-abnormalities"
                   data-component="body"             >
            <code>false</code>
        </label>
    <br>
<p>Example: <code>false</code></p>
        </div>
        </form>

                    <h2 id="visual-board-domain-GETapi-v1-visual-board-abnormalities--id-">Detail Abnormality</h2>

<p>
</p>

<p>Mengambil detail lengkap suatu masalah beserta progressnya.</p>

<span id="example-requests-GETapi-v1-visual-board-abnormalities--id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/visual-board/abnormalities/architecto" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/visual-board/abnormalities/architecto"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-visual-board-abnormalities--id-">
            <blockquote>
            <p>Example response (500):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Server Error&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-visual-board-abnormalities--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-visual-board-abnormalities--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-visual-board-abnormalities--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-visual-board-abnormalities--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-visual-board-abnormalities--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-visual-board-abnormalities--id-" data-method="GET"
      data-path="api/v1/visual-board/abnormalities/{id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-visual-board-abnormalities--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-visual-board-abnormalities--id-"
                    onclick="tryItOut('GETapi-v1-visual-board-abnormalities--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-visual-board-abnormalities--id-"
                    onclick="cancelTryOut('GETapi-v1-visual-board-abnormalities--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-visual-board-abnormalities--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/visual-board/abnormalities/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-visual-board-abnormalities--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-visual-board-abnormalities--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id"                data-endpoint="GETapi-v1-visual-board-abnormalities--id-"
               value="architecto"
               data-component="url">
    <br>
<p>ULID dari abnormality. Example: <code>architecto</code></p>
            </div>
                    </form>

                    <h2 id="visual-board-domain-DELETEapi-v1-visual-board-abnormalities--id-">Hapus Abnormality</h2>

<p>
</p>

<p>Menghapus catatan abnormality (menggunakan soft deletes).</p>

<span id="example-requests-DELETEapi-v1-visual-board-abnormalities--id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://localhost:8000/api/v1/visual-board/abnormalities/architecto" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/visual-board/abnormalities/architecto"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-visual-board-abnormalities--id-">
</span>
<span id="execution-results-DELETEapi-v1-visual-board-abnormalities--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-visual-board-abnormalities--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-visual-board-abnormalities--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-visual-board-abnormalities--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-visual-board-abnormalities--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-visual-board-abnormalities--id-" data-method="DELETE"
      data-path="api/v1/visual-board/abnormalities/{id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-visual-board-abnormalities--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-v1-visual-board-abnormalities--id-"
                    onclick="tryItOut('DELETEapi-v1-visual-board-abnormalities--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-v1-visual-board-abnormalities--id-"
                    onclick="cancelTryOut('DELETEapi-v1-visual-board-abnormalities--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-v1-visual-board-abnormalities--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/visual-board/abnormalities/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-visual-board-abnormalities--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-visual-board-abnormalities--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id"                data-endpoint="DELETEapi-v1-visual-board-abnormalities--id-"
               value="architecto"
               data-component="url">
    <br>
<p>ULID dari abnormality. Example: <code>architecto</code></p>
            </div>
                    </form>

                    <h2 id="visual-board-domain-PATCHapi-v1-visual-board-abnormalities--id--progress">Update Progress Abnormality</h2>

<p>
</p>

<p>Menyimpan progres perbaikan (0-100%) dan aktual dari penanggulangan masalah.
Jika persentase 100, status otomatis berubah menjadi 'resolved'.</p>

<span id="example-requests-PATCHapi-v1-visual-board-abnormalities--id--progress">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PATCH \
    "http://localhost:8000/api/v1/visual-board/abnormalities/architecto/progress" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"progress_percentage\": 1,
    \"countermeasure_actual\": \"n\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/visual-board/abnormalities/architecto/progress"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "progress_percentage": 1,
    "countermeasure_actual": "n"
};

fetch(url, {
    method: "PATCH",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PATCHapi-v1-visual-board-abnormalities--id--progress">
</span>
<span id="execution-results-PATCHapi-v1-visual-board-abnormalities--id--progress" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PATCHapi-v1-visual-board-abnormalities--id--progress"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PATCHapi-v1-visual-board-abnormalities--id--progress"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PATCHapi-v1-visual-board-abnormalities--id--progress" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PATCHapi-v1-visual-board-abnormalities--id--progress">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PATCHapi-v1-visual-board-abnormalities--id--progress" data-method="PATCH"
      data-path="api/v1/visual-board/abnormalities/{id}/progress"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PATCHapi-v1-visual-board-abnormalities--id--progress', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PATCHapi-v1-visual-board-abnormalities--id--progress"
                    onclick="tryItOut('PATCHapi-v1-visual-board-abnormalities--id--progress');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PATCHapi-v1-visual-board-abnormalities--id--progress"
                    onclick="cancelTryOut('PATCHapi-v1-visual-board-abnormalities--id--progress');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PATCHapi-v1-visual-board-abnormalities--id--progress"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/v1/visual-board/abnormalities/{id}/progress</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PATCHapi-v1-visual-board-abnormalities--id--progress"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PATCHapi-v1-visual-board-abnormalities--id--progress"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id"                data-endpoint="PATCHapi-v1-visual-board-abnormalities--id--progress"
               value="architecto"
               data-component="url">
    <br>
<p>ULID dari abnormality. Example: <code>architecto</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>progress_percentage</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="progress_percentage"                data-endpoint="PATCHapi-v1-visual-board-abnormalities--id--progress"
               value="1"
               data-component="body">
    <br>
<p>Must be at least 0. Must not be greater than 100. Example: <code>1</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>countermeasure_actual</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="countermeasure_actual"                data-endpoint="PATCHapi-v1-visual-board-abnormalities--id--progress"
               value="n"
               data-component="body">
    <br>
<p>Must not be greater than 1000 characters. Example: <code>n</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>pic_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="pic_id"                data-endpoint="PATCHapi-v1-visual-board-abnormalities--id--progress"
               value=""
               data-component="body">
    <br>
<p>Must match an existing stored value.</p>
        </div>
        </form>

            

        
    </div>
    <div class="dark-box">
                    <div class="lang-selector">
                                                        <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                                        <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                            </div>
            </div>
</div>
</body>
</html>
