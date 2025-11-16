// =============================
//   INGRESOS POR MES
// =============================
let labels1 = datosIngresos.map(i => i.mes);
let data1 = datosIngresos.map(i => i.ingresos);

new Chart(document.getElementById('grafIngresos'), {
    type: 'bar',
    data: {
        labels: labels1,
        datasets: [{
            label: 'Ingresos',
            data: data1
        }]
    }
});

// =============================
//   TENDENCIA DE OCUPACIÓN
// =============================
new Chart(document.getElementById('grafOcupacion'), {
    type: 'line',
    data: {
        labels: dias,
        datasets: [{
            label: 'Habitaciones Ocupadas',
            data: ocupacion,
            borderWidth: 2
        }]
    }
});
