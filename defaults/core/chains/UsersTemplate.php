
<!-- Main Content -->
<main>
    {inner}
</main>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Source+Sans+3:ital,wght@0,200..900;1,200..900&display=swap');

    :root {
        --foreground: #333;
        --background: #ddd;
        --highlight: #2d2d2d;
        --feint: #fdfdfd;
    }

    section#auth {font-family: "Source Sans 3", sans-serif;font-weight:100;text-align:center; width:400px;padding-bottom:28px;position: absolute;top:50%;left:50%;transform: translate(-50%, -50%);border:1px solid var(--background);color:var(--foreground);background:var(--feint);border-radius:12px;}
    section#auth div.input{margin:25px 16px;}
    section#auth h3{margin:30px auto 10px;font-weight:400;}
    section#auth p{margin:10px auto 20px;}
    section#auth p.error{color: #a53535;background:#FFBFBF37;font-weight:600;width:260px;border-radius:8px;margin:auto;padding:3px 7px 5px;border:1px solid #A53535BE;}
    section#auth p.error svg {position:relative;top:4px;margin-right:2px;}
    section#auth label{display:block;margin:10px auto;}
    section#auth input {width:300px;padding:10px 12px;border-radius:5px;border:1px solid var(--background);color:var(--foreground);}
    section#auth input[type=submit]{width:150px;margin:10px auto;color:var(--foreground);cursor:pointer}
    section#auth a, section#auth a:visited, section#auth a:hover, section#auth a:active{color:var(--foreground);text-decoration:none;font-size:14px;padding:0 8px;}
    section#auth input#yf-goHome{margin-top:17px;}
    div.g-recaptcha {margin: 20px auto 12px;width:304px;}

    #spinner-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }

    #spinner-overlay.active {
        display: flex;
    }

    .spinner {
        width: 50px;
        height: 50px;
        border: 5px solid var(--background);
        border-top: 5px solid var(--foreground);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }

</style>

<script>

    // Create spinner overlay
    const spinnerOverlay = document.createElement('div');
    spinnerOverlay.id = 'spinner-overlay';
    spinnerOverlay.innerHTML = '<div class="spinner"></div>';
    document.body.appendChild(spinnerOverlay);

    function showSpinner() {
        spinnerOverlay.classList.add('active');
    }

    function hideSpinner() {
        spinnerOverlay.classList.remove('active');
    }

    function executeScripts(html) {
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        const scripts = tempDiv.querySelectorAll('script');

        scripts.forEach(function (oldScript) {
            const newScript = document.createElement('script');

            // Copy attributes
            Array.from(oldScript.attributes).forEach(function (attr) {
                newScript.setAttribute(attr.name, attr.value);
            });

            // Copy inline content if no src
            if (!oldScript.src) {
                newScript.textContent = oldScript.textContent;
            }

            document.head.appendChild(newScript);

            // Remove after execution to avoid duplicates
            if (!oldScript.src) {
                document.head.removeChild(newScript);
            }
        });
    }

    function initializeAuthSection() {
        const form = document.querySelector('section#auth form');

        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(form);
                const url = form.getAttribute('action');

                showSpinner();

                // Alternative way using XMLHttpRequest:
                const xhr = new XMLHttpRequest();
                xhr.open('POST', url, true);
                xhr.onload = function () {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        const authSection = document.querySelector('section#auth');
                        if (authSection) {
                            authSection.outerHTML = xhr.responseText;
                            executeScripts(xhr.responseText);
                            initializeAuthSection();
                        }
                        hideSpinner();
                    } else {
                        console.error('Error:', xhr.statusText);
                        hideSpinner();
                    }
                };
                xhr.onerror = function () {
                    console.error('Error:', xhr.statusText);
                    hideSpinner();
                };
                xhr.send(formData);
            });
        }
    }
    


    const anchors = document.querySelectorAll('section#auth a');

    anchors.forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();

            const url = anchor.getAttribute('href');

            showSpinner();

            const xhr = new XMLHttpRequest();
            xhr.open('GET', url, true);
            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    const authSection = document.querySelector('section#auth');
                    if (authSection) {
                        authSection.outerHTML = xhr.responseText;
                        executeScripts(xhr.responseText);
                        initializeAuthSection();
                    }
                    hideSpinner();
                } else {
                    console.error('Error:', xhr.statusText);
                    hideSpinner();
                }
            };
            xhr.onerror = function() {
                console.error('Error:', xhr.statusText);
                hideSpinner();
            };
            xhr.send();

        });
    });


document.addEventListener('DOMContentLoaded', function () {
    initializeAuthSection();
});

</script>