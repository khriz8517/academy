Vue.component("modal", {
    props: ["total_points"],
    methods: {
        toggleModal: function () {
            this.$emit("toggle-modal");
        },
    },
    computed: {
        modal_title: function () {
            return this.total_points > 0
                ? "Felicidades tienes " + this.total_points + " puntos"
                : "Bienvenido a SAP academy";
        },
    },
    template: `
        <div class="modal-mask">
            <div class="modal-wrapper">
                <div class="modal-container">
                    <h3>{{modal_title}}</h3>
                    <p>Completa cursos para ganar puntos y desbloquear módulos.</p>
                    <div class="modal-footer">
                        <a href="#" class="modal-btn green-grad_btn" @click="toggleModal()">Continuar</a>
                    </div>
                </div>
            </div>
        </div>`,
});

var app = new Vue({
    delimiters: ["{(", ")}"],
    el: "#app",
    data: {
        searching: false,
        search_value: "",
        search_count: 0,
        showModal: false,
        showTableDetails: false,
        showPanels: false,
        coursesByModuleId: 0,
        total_points: 0,
        welcome: {
            head: "Bienvenido a",
            title: "sap academy",
            foot: "Completa cursos y gana puntos.",
        },
        chart_data: {
            chart_completed: 0,
            chart_inprogress: 0,
            chart_nostarted: 0,
        },
        show_extra_courses: false,
        modules_completed: 0,
        selected_module_id: 0,
        selected_category_text: "",
        modulos: [],
        cursos_pool: [],
        extra_cursos_pool: [],
        cursos_pool_bk: [
            {
                id: 1,
                mod_id: 1,
                curso: "Nombre del Curso",
                curso_img: "./images/curso.jpg",
                link: "#",
                progress: 70,
                estado: 1,
                nota: 50,
                intentos: 1,
            },
            {
                id: 2,
                mod_id: 1,
                curso: "Nombre del Curso",
                curso_img: "./images/curso.jpg",
                link: "#",
                progress: 50,
                estado: 0,
                nota: 50,
                intentos: 1,
            },
            {
                id: 3,
                mod_id: 1,
                curso: "Nombre del Curso",
                curso_img: "./images/curso.jpg",
                link: "#",
                progress: 30,
                estado: 0,
                nota: 50,
                intentos: 1,
            },
            {
                id: 4,
                mod_id: 2,
                curso: "Nombre del Curso",
                curso_img: "./images/curso.jpg",
                link: "#",
                progress: 10,
                estado: 0,
                nota: 50,
                intentos: 1,
            },
            {
                id: 5,
                mod_id: 2,
                curso: "Nombre del Curso",
                curso_img: "./images/curso.jpg",
                link: "#",
                progress: 90,
                estado: 0,
                nota: 50,
                intentos: 1,
            },
            {
                id: 6,
                mod_id: 3,
                curso: "Nombre del Curso",
                curso_img: "./images/curso.jpg",
                link: "#",
                progress: 50,
                estado: 0,
                nota: 50,
                intentos: 1,
            },
            {
                id: 7,
                mod_id: 3,
                curso: "Nombre del Curso",
                curso_img: "./images/curso.jpg",
                link: "#",
                progress: 30,
                estado: 0,
                nota: 50,
                intentos: 1,
            },
            {
                id: 8,
                mod_id: 4,
                curso: "Nombre del Curso",
                curso_img: "./images/curso.jpg",
                link: "#",
                progress: 10,
                estado: 0,
                nota: 50,
                intentos: 1,
            },
            {
                id: 9,
                mod_id: 5,
                curso: "Nombre del Curso",
                curso_img: "./images/curso.jpg",
                link: "#",
                progress: 90,
                estado: 0,
                nota: 50,
                intentos: 1,
            },
            {
                id: 10,
                mod_id: 5,
                curso: "Nombre del Curso",
                curso_img: "./images/curso.jpg",
                link: "#",
                progress: 50,
                estado: 0,
                nota: 50,
                intentos: 1,
            },
            {
                id: 11,
                mod_id: 5,
                curso: "Nombre del Curso",
                curso_img: "./images/curso.jpg",
                link: "#",
                progress: 0,
                estado: 0,
                nota: 50,
                intentos: 1,
            },
            {
                id: 12,
                mod_id: 6,
                curso: "Nombre del Curso",
                curso_img: "./images/curso.jpg",
                link: "#",
                progress: 100,
                estado: 0,
                nota: 50,
                intentos: 1,
            },
            {
                id: 13,
                mod_id: 6,
                curso: "Nombre del Curso",
                curso_img: "./images/curso.jpg",
                link: "#",
                progress: 100,
                estado: 0,
                nota: 50,
                intentos: 1,
            },
        ],
    },
    created() {
        this.getModulos();
        this.getCoursesByUser();
        this.getAllCourses();
    },
    methods: {
        getModulos: function () {
            let frm = new FormData();
            frm.append("request_type", "getModulos");
            axios.post("api/ajax_controller.php", frm).then((res) => {
                this.modulos = res.data;
                res.data.forEach((element) => {
                    element.progress == 100
                        ? (this.modules_completed += 1)
                        : null;
                });
            });
        },
        getCoursesByUser: function () {
            let frm = new FormData();
            frm.append("request_type", "getCoursesByUser");
            axios.post("api/ajax_controller.php", frm).then((res) => {
                this.cursos_pool = res.data;
                this.chart_data.chart_completed = res.data.filter(
                    (item) => item.progress == 100
                ).length;
                this.chart_data.chart_inprogress = res.data.filter(
                    (item) => item.progress > 0 && item.progress < 100
                ).length;
                this.chart_data.chart_nostarted = res.data.filter(
                    (item) => item.progress == 0
                ).length;
                this.donutChart(this.chart_data);
                console.log("this.chart_data mounted");
            });
        },
        getCourses: function (idmod, param) {
            // console.log(idmod, param);
            switch (param) {
                case 0:
                    let completed = this.cursos_pool.filter(
                        (item) => item.mod_id == idmod && item.progress == 100
                    ).length;
                    return completed;
                case 1:
                    let inprogress = this.cursos_pool.filter(
                        (item) =>
                            item.mod_id == idmod &&
                            item.progress > 0 &&
                            item.progress < 100
                    ).length;
                    return inprogress;
                case 2:
                    let nostarted = this.cursos_pool.filter(
                        (item) => item.mod_id == idmod && item.progress == 0
                    ).length;
                    return nostarted;
                case 3:
                    let cursos = this.cursos_pool.filter(
                        (item) => item.mod_id == idmod
                    );
                    let average =
                        cursos.reduce((total, next) => total + next.nota, 0) /
                        cursos.length;
                    return average ? average : 0;
                default:
                    return "Por definir";
            }
        },
        getCoursesByModuleId: function (id) {
            return this.cursos_pool.filter((item) => item.mod_id === id);
        },
        getEstado: function (estado) {
            switch (estado) {
                case 0:
                    return "No iniciado";
                case 1:
                    return "En proceso";
                case 2:
                    return "Completado";
                default:
                    return "Error";
            }
        },
        donutChart: function (data) {
            var ctx = document.getElementById("myChart");
            var myChart = new Chart(ctx, {
                type: "doughnut",
                data: {
                    labels: [
                        "Completados (" + data.chart_completed + " cursos)",
                        "En progreso (" + data.chart_inprogress + " cursos)",
                        "No iniciados (" + data.chart_nostarted + " cursos)",
                    ],
                    datasets: [
                        {
                            label: "Progreso",
                            data: [
                                data.chart_completed,
                                data.chart_inprogress,
                                data.chart_nostarted,
                            ],
                            backgroundColor: ["#147ad6", "#79d2de", "#ff715b"],
                            hoverOffset: 4,
                        },
                    ],
                },
            });
        },
        getAllCourses: function () {
            let frm = new FormData();
            frm.append("request_type", "getAllCourses");
            axios.post("api/ajax_controller.php", frm).then((res) => {
                this.extra_cursos_pool = res.data;
            });
        },
        getExtraCoursesByModule: function () {
            console.log("extra courses");
        },
        toggleModal: function () {
            this.showModal = !this.showModal;
        },
    },
    computed: {
        progressbar: function () {
            return (this.modules_completed * 100) / this.modulos.length;
        },
        stages: function () {
            var array = [{ number: 0, value: 0, estado: 1 }];
            var stage_points = 0;
            for (let i = 0; i < this.modulos.length; i++) {
                const element = this.modulos[i];
                const index = i + 1;
                array.push({
                    number: index,
                    value: (stage_points += 50),
                    estado: this.modules_completed >= index ? 1 : 0,
                });
            }
            return array;
        },
        cursos: function () {
            return this.getCoursesByModuleId(this.selected_module_id);
        },
        detalleModulo: function () {
            return this.getCoursesByModuleId(this.coursesByModuleId);
        },
        extra_cursos: function () {
            return this.extra_cursos_pool.filter(
                (item) => item.mod_id === this.selected_module_id
            );
        },
        moduloName: function () {
            if (this.coursesByModuleId) {
                return this.modulos.filter(
                    (item) => item.id == this.coursesByModuleId
                )[0].mod_short;
            } else {
                return "Selecciona modulo";
            }
        },
        cursos_search: function () {
            return this.cursos_pool.filter((item) =>
                item.curso.toLowerCase().includes(this.search_value)
            );
        },
    },
});
