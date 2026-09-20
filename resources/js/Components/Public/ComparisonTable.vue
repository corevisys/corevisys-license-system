<script setup>
defineProps({
    columns: { type: Array, default: () => [] },
    rows: { type: Array, default: () => [] },
});
</script>

<template>
    <div class="overflow-x-auto rounded-[10px] border border-panel-line">
        <table class="w-full min-w-[620px] border-collapse text-left text-[13.5px]">
            <thead>
                <tr class="bg-panel-2">
                    <th
                        v-for="column in columns"
                        :key="column.key"
                        scope="col"
                        class="border-b border-panel-line px-4 py-3 font-mono text-[11.5px] uppercase tracking-wide text-text-muted"
                    >{{ column.label }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(row, index) in rows" :key="index" class="odd:bg-panel-2/40">
                    <th
                        scope="row"
                        class="border-b border-panel-line px-4 py-3 text-left font-medium text-text-primary"
                    >{{ row.feature }}</th>
                    <td
                        v-for="column in columns.slice(1)"
                        :key="column.key"
                        class="border-b border-panel-line px-4 py-3 text-text-secondary"
                    >{{ row[column.key] }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>