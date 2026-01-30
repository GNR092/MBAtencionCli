 window.addEventListener("load", function() {
    // Mostrar popup después de 3 segundos
    setTimeout(function() {
      let popup = document.getElementById("myPopup");

      // Quita "hidden" y activa fade-in
      popup.classList.remove("hidden");
      setTimeout(() => popup.classList.add("opacity-100"), 50);

      // Cierra automáticamente después de 5 segundos
      setTimeout(function() {
        // Empieza animación fade-out
        popup.classList.remove("opacity-50");
        popup.classList.add("opacity-0");

        // Cuando termine la animación (0.5s), ocultamos el div
        setTimeout(() => {
          popup.classList.add("hidden");
        }, 500);
      }, 5000); // 5000ms = 5 segundos
    }); // 1000ms = 1 segundos
  });