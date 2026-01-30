    async function checkPassword() {
            let el = document.querySelector('[x-data]');
            let data = Alpine.$data(el);

        let response = await fetch("/password-check", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
            },
            body: JSON.stringify({ password: data.password })
        });


        if (response.ok) {
            data.show = false;
            let url = "/contratos/descargar/" + data.docId;
            window.location.href = url;
        } else {
            let text = await response.text(); 
            try {
                let result = JSON.parse(text);
                data.error = result.message ?? "Error de verificación";
            } catch {
                data.error = "Error inesperado: " + text;
            }
        }


    }
