document.addEventListener('DOMContentLoaded', function() {
    const myInput = document.getElementById("myInput");
    const myUL = document.getElementById("myUL");
    const selectedUserId = document.getElementById("selectedUserId");
    const proyectSelect = document.getElementById("proyect");

    function filterUsers() {
        const filter = myInput.value.toUpperCase();
        const li = myUL.getElementsByTagName("li");

        if (filter.length > 0) {
            myUL.classList.remove("hidden");
        } else {
            myUL.classList.add("hidden");
        }

        for (let i = 0; i < li.length; i++) {
            const a = li[i].getElementsByTagName("a")[0];
            const txtValue = a.textContent || a.innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                li[i].style.display = "";
            } else {
                li[i].style.display = "none";
            }
        }
    }

    if (myInput) {
        myInput.addEventListener('keyup', filterUsers);

        myInput.addEventListener('focus', function() {
            if (this.value) {
                filterUsers();
            }
        });
    }

    // This function can be called from the `onclick` attribute in the blade file
    window.selectUser = function(id, name) {
        if (selectedUserId && myInput && myUL) {
            selectedUserId.value = id;
            myInput.value = name;
            myUL.classList.add("hidden");

            // Fetch projects for the selected user
            if (proyectSelect) {
                fetch(`/api/users/${id}/projects`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(projects => {
                        // Clear existing options
                        proyectSelect.innerHTML = '';

                        // Add default option
                        const defaultOption = document.createElement('option');
                        defaultOption.value = "";
                        defaultOption.textContent = "Selecciona proyecto";
                        defaultOption.disabled = true;
                        defaultOption.selected = true;
                        proyectSelect.appendChild(defaultOption);

                        // Populate with new projects
                        if (projects.length > 0) {
                            projects.forEach(project => {
                                const option = document.createElement('option');
                                option.value = project.id_proyecto;
                                option.textContent = project.nombre_proyecto;
                                proyectSelect.appendChild(option);
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching projects:', error);
                        proyectSelect.innerHTML = '';
                         const errorOption = document.createElement('option');
                        errorOption.value = "";
                        errorOption.textContent = "Error al cargar proyectos";
                        errorOption.disabled = true;
                        errorOption.selected = true;
                        proyectSelect.appendChild(errorOption);
                    });
            }
        }
    }

    document.addEventListener('click', function(event) {
        if (myUL && myInput && !myUL.contains(event.target) && !myInput.contains(event.target)) {
            myUL.classList.add('hidden');
        }
    });
});
