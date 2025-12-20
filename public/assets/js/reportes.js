// ======================================================
//   GRÁFICA: INGRESOS POR MES (BARRAS)
// ======================================================

const ctxIngresos = document.getElementById("grafIngresos");

if (ctxIngresos) {

    // Convertir "2025-11" → "Nov 2025"
    const meses = datosIngresos.map(x => {
        const [year, month] = x.mes.split("-");
        const fecha = new Date(year, month - 1);
        return fecha.toLocaleDateString("es-MX", { year: "numeric", month: "short" });
    });

    const valores = datosIngresos.map(x => Number(x.ingresos));

    new Chart(ctxIngresos, {
        type: 'bar',
        data: {
            labels: meses,
            datasets: [{
                label: "Ingresos ($ MXN)",
                data: valores,
                backgroundColor: "#4F46E5",
                borderRadius: 6
            }]
        },
        options: {
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: v => "$" + v }
                }
            }
        }
    });
}



// ======================================================
//   GRÁFICA: TENDENCIA DE OCUPACIÓN (LÍNEA)
// ======================================================

const ctxOcup = document.getElementById("grafOcupacion");

if (ctxOcup) {

    const porcentaje = ocupacion.map(o =>
        totalHabitaciones > 0 
            ? Math.round((o / totalHabitaciones) * 100)
            : 0
    );

    // Formatear días como "17 Nov"
    const diasLabel = dias.map(d => {
        let f = new Date(d);
        return f.toLocaleDateString("es-MX", { day: "numeric", month: "short" });
    });

    new Chart(ctxOcup, {
        type: 'line',
        data: {
            labels: diasLabel,
            datasets: [{
                label: "Ocupación (%)",
                data: porcentaje,
                borderColor: "#10B981",
                backgroundColor: "rgba(16,185,129,0.2)",
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: "#10B981"
            }]
        },
        options: {
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: { callback: v => v + "%" }
                }
            }
        }
    });
}
