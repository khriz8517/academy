Vue.component("modal", {
    props: ["procesos_item"],
    methods: {
        toggleModal: function () {
            this.$emit("toggle-modal");
        },
    },
    template: `
        <div class="modal-mask">
            <div class="modal-wrapper">
                <div class="modal-container" style="height: 35em">
                    <div @click="toggleModal" class="modal-close"><span class="material-icons">cancel</span></div>
                    <object :data="procesos_item.link_file" type="application/pdf" width="100%" height="100%">
                        <p>En caso no se visualice el PDF has click <a :href="procesos_item.link_file">aqui!</a></p>
                    </object>
                </div>
            </div>
        </div>`,
});

var app = new Vue({
    el: "#app",
    delimiters: ["{(", ")}"],
    data: {
        showModal: false,
        procesos_item: {},
        showlinkconditions: {},
        showlinkobj: false,
        procesos: [
            {
                id: 1,
                proceso_title: "Nombre del primer archivo adjunto",
                updated: "Actualizado el 16 de junio de 2021",
                material_icon: "picture_as_pdf",
                link_file: "http://www.africau.edu/images/default/sample.pdf",
                format: ".pdf",
            },
            {
                id: 2,
                proceso_title: "Nombre del primer archivo adjunto",
                updated: "Actualizado el 16 de junio de 2021",
                material_icon: "picture_as_pdf",
                link_file: "http://www.africau.edu/images/default/sample.pdf",
                format: ".pdf",
            },
            {
                id: 3,
                proceso_title: "Nombre del primer archivo adjunto",
                updated: "Actualizado el 16 de junio de 2021",
                material_icon: "picture_as_pdf",
                link_file: "http://www.africau.edu/images/default/sample.pdf",
                format: ".pdf",
            },
            {
                id: 4,
                proceso_title: "Nombre del primer archivo adjunto",
                updated: "Actualizado el 16 de junio de 2021",
                material_icon: "picture_as_pdf",
                link_file: "http://www.africau.edu/images/default/sample.pdf",
                format: ".pdf",
            },
        ],
    },
    created() {
        this.showlinkobjvalues();
    },
    methods: {
        showlinkobjvalues: function () {
            this.procesos.forEach((item) => {
                this.showlinkconditions[item.id] = false;
            });
        },
        showlinkfunc: function () {
            for (const prop in this.showlinkconditions) {
                console.log(this.showlinkconditions[prop]);
                if (this.showlinkconditions[prop] === false) {
                    this.showlinkobj = false;
                    return;
                }
            }
            this.showlinkobj = true;
        },
        toggleModal: function () {
            this.showModal = !this.showModal;
        },
        scrollToElement: function (goTo) {
            const el = this.$el.getElementsByClassName(goTo)[0];

            if (el) {
                el.scrollIntoView({ behavior: "smooth" });
            }
        },
    },
});
