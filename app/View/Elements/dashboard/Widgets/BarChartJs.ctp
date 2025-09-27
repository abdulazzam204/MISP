<?php

echo $this->element('genericElements/assetLoader', [
    'js' => ['Chart.min']
]);

$randomNumber = rand();

// Prepare labels and data
$labels = json_encode(array_keys($data['data']));
$values = json_encode(array_values($data['data']));
if (isset($data['links'])) {
    $links = json_encode($data['links']);
}

// Handle colors (fallback to a default color if not provided)
$colors = !empty($data['colors']) ? $data['colors'] : '#0088cc';

// Handle logarithmic option
if (!empty($data['logarithmic'])) {
    $values = json_encode(array_values($data['logarithmic']));
    
} elseif (!empty($config['widget_config']['forceLogarithm'])) {
    $values = array_map(function ($v) {
        return $v == 1 ? 0.1 : log10($v);
    }, $values);
}
?>

<div id="barChartContainer-<?= $randomNumber?>" style="position: relative; width: 100%; height: 100%;">
    <canvas id="threatActorChart-<?= $randomNumber?>"></canvas>
</div>

<script>
    (function(){
        const chartId = 'threatActorChart-<?= $randomNumber?>'
        const ctx = document.getElementById(chartId).getContext('2d');
        Chart.defaults.font.family = "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif";
        Chart.defaults.color = '#000000';
        const urls = <?= $links ?>;

        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= $labels ?>,
                datasets: [{
                    label: 'Threat Actor Events<?= !empty($data['output_decorator']) ? ' (' . $data['output_decorator'] . ')' : '' ?>',
                    data: <?= $values ?>,
                    backgroundColor: <?= json_encode($colors) ?>,
                    borderRadius: 6
                }]
            },
            options: {
                indexAxis: 'y', 
                responsive: true,
                maintainAspectRatio: false,
                onClick: (evt, elements) => {
                    if (elements.length > 0) {
                        const chartElement = elements[0];
                        const label = chart.data.labels[chartElement.index];
                        if (urls[label]) {
                            window.location.href = urls[label];
                        } else {
                            alert('No events found for ' + label);
                        }
                    }
                },
                plugins: {
                    legend: { 
                        display: false,
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                return value + '<?= !empty($data['output_decorator']) ? $data['output_decorator'] : '' ?>';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        title: {<?= isset($data['axis']['x']) ? 'display: true, text: \''.$data['axis']['x'].'\'' : 'display: false'; ?>},
                        ticks: { stepSize: 1 }
                    },
                    y: {
                        title: {<?= isset($data['axis']['y']) ? 'display: true, text: \''.$data['axis']['y'].'\'' : 'display: false'; ?>},
                    }
                }
            }
        });

        // Add ResizeObserver for smooth resizing when widget size changes
        const container = document.getElementById(chartId).parentElement;
        if (window.ResizeObserver) {
            const resizeObserver = new ResizeObserver(() => {
                chart.resize();
            });
            resizeObserver.observe(container);
        } else {
            // fallback for older browsers
            $(window).on('resize', () => chart.resize());
        }        
    }) ();
</script>
