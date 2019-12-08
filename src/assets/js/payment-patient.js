var patient_id = document.querySelector(".patientId").textContent;

if (patient_id) {
    function showCards() {
        $.ajax({
            // url: `https://buscamed.do/webservice/vc_list_cards/${patient_id}`,
            url: `http://localhost/get_patient_cards/${patient_id}`,
            success: function(data) {
                document.querySelector(".cards").innerHTML = "";

                if (data.length > 0) {
                    data.map(el => {
                        let checked = el.preferred == true ? "checked" : "";
                        let preferred =
                            el.preferred == true
                                ? '<i class="fa fa-check-square-o" style="color: #2dd4ac; font-size: 20px; font-weight: bold; margin-left: 5%;"></i>'
                                : "";
                        let card_number = el.card_hash.replace(/\*/g, "x");
                        var cardName;
                        // el.type = "002";
                        switch (el.type) {
                            case "001":
                                cardName = "Visa";
                                break;
                            case "002":
                                cardName = "MasterCard";
                                break;
                        }

                        document.querySelector(".cards").insertAdjacentHTML(
                            "beforeend",
                            `<div class="inputs_general">
                                <div class="checkbox">
                                    <input type="radio" class='check_preferred' name="preferred" value="${el.id}" ${checked} />
                                </div>
                                <div class="inputs_2" style="display:flex; justify-content: space-between;">
                                    <p>${card_number}</p>
                                    <div style='display: flex; justify-content: space-between;'>
                                        <img id='card_logo' src='../src/assets/images/${cardName}.svg' title='${cardName}' alt='${cardName}'>
                                        ${preferred}
                                    </div>
                                </div>
                            </div>

                            <div class="delete_pay_method btn_delete">
                                <button id=${el.id} class='btn_delete_method btn-danger'>Eliminar</button>
                            </div>`
                        );
                    });
                } else {
                    document.querySelector(".cards").insertAdjacentHTML(
                        "beforeend",
                        `<div class="inputs_general">
                            <div class="inputs_2" style="display:flex; justify-content: space-between">
                                <p>Agregue su tarjeta preferida para pagar sus citas de manera rápida y segura</p>
                            </div>
                        </div>`
                    );
                }

                $(".dots-container").fadeOut(100);
                $(".cards").fadeIn(300);
            }
        });
    }

    function deleteCard(card_id) {
        $.ajax({
            type: "delete",
            // url: `https://buscamed.do/webservice/vc_delete_card/${patient_id}/${card_id}`,
            url: `http://localhost/delete_card/${patient_id}/${card_id}`,
            success: function(data) {
                swal({
                    title: "Tarjeta Eliminada",
                    icon: "success"
                }).then(() => showCards());
            }
        });
    }

    // fillCardType();
    showCards();

    // uiElementsPerfil.btnAddCard.addEventListener("click", ev => {
    //     addCard();
    // });

    document.querySelector(".cards").addEventListener("click", ev => {
        if (ev.target.getAttribute("type") === "radio" && !ev.target.hasAttribute("checked")) {
            $.ajax({
                type: "POST",
                // url: `https://buscamed.do/webservice/vc_make_preferred_card/${patient_id}/${ev.target.value}`,
                url: `http://localhost/vc_make_preferred_card/${patient_id}/${ev.target.value}`,
                success: function(data) {
                    const space = document.createElement("div");
                    space.style.marginBottom = "5%";

                    swal({
                        title: "Tarjeta seleccionada para realizar pagos",
                        icon: "success",
                        buttons: false,
                        content: space,
                        timer: 2000
                    }).then(() => {
                        showCards();
                    });
                }
            });
        }

        if (ev.target.classList.contains("btn_delete_method")) {
            swal({
                title: "¿Estás seguro de eliminar esta tarjeta?",
                icon: "warning",
                buttons: ["Cancelar", "Si"],
                dangerMode: true
            }).then(willDelete => {
                if (willDelete) {
                    $(".dots-container").fadeIn(300);
                    $(".cards").fadeOut(100);
                    deleteCard(ev.target.id);
                }
            });
        }
    });

    $("#exampleModal").on("hidden.bs.modal", function(e) {
        document.getElementById("iframe_from").src = `${base_url}Webservice/create_token_patient/${patient_id}`;
        showCards();
    });

    window.closeIFrame = function() {
        $("#exampleModal").modal("hide");
    };
}
