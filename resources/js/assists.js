import axios from "axios";
import ExcelJS from "exceljs";
import moment from "moment";
moment.locale("es");
import "moment/locale/es";

document.addEventListener("DOMContentLoaded", async () => {
    $ = document.querySelector.bind(document);
    const $perSchedule = $("#button-export-assists-per-schedule");
    const $peerUser = $("#button-export-assists-peer-user");
    const $centralized = $("#button-export-assists-centralized");
    const $snSchedules = $("#export-assists-sn-schedules");
    const $withoutCalculating = $("#export-assists-without-calculating");

    const $checkServer = $("#check-server");
    const $errorServer = $("#error-server");

    if ($checkServer) {
        $checkServer.innerHTML = "Verificando...";
        const { data } = await axios.get("/api/assists/check-server");
        if (data.status == "error") {
            $checkServer.innerHTML = data.message;
            $errorServer.innerHTML = data.error;
            $checkServer.classList.add("text-red-500");
        } else {
            $checkServer.innerHTML = data.message;
            $checkServer.classList.add("text-green-500");
        }
    }

    async function exportAssists(groupAssists) {
        const workbook = new ExcelJS.Workbook();
        const response = await fetch("/templates/assists.xlsx");
        const arrayBuffer = await response.arrayBuffer();

        if (!arrayBuffer) return;

        await workbook.xlsx.load(arrayBuffer);
        const worksheet = workbook.getWorksheet("Asistencias");

        let rr = 4;

        groupAssists.forEach((item, i) => {
            item.assists.forEach((assist, ii) => {
                const index = i + 1 < 10 ? `0${i + 1}` : i + 1;
                const row = worksheet.getRow(rr);
                rr++;
                row.getCell(2).value = ii === 0 ? index : "";
                row.getCell(3).value = ii === 0 ? item.dni : "";
                row.getCell(4).value = ii === 0 ? item.full_name : "";
                row.getCell(5).value = ii === 0 ? assist.role : "";
                row.getCell(6).value = ii === 0 ? assist.job_position : "";
                row.getCell(7).value = assist.title;
                row.getCell(8).value = assist.date;
                if (assist.date) {
                    row.getCell(8).numFmt = "dd/mm/yyyy";
                }

                row.getCell(9).value = assist.day;
                row.getCell(10).value = assist.turn;
                row.getCell(11).value = moment(assist.from).format("HH:mm:ss");
                row.getCell(11).numFmt = "hh:mm:ss"; // Excel time format
                row.getCell(12).value = moment(assist.to).format("HH:mm:ss");
                row.getCell(12).numFmt = "hh:mm:ss"; // Excel time format
                row.getCell(13).value = assist.marked_in;
                row.getCell(14).value = assist.marked_out;
                row.getCell(15).value = assist.owes_time;
                row.getCell(16).value = assist.terminal?.database_name;
                row.getCell(17).value = assist.observations;
            });
        });

        worksheet.columns.forEach((column) => {
            let maxLength = 0;
            column.eachCell({ includeEmpty: true }, (cell) => {
                const cellValue = cell.value ? cell.value.toString() : "";
                maxLength = Math.max(maxLength, cellValue.length);
            });
            column.width = maxLength < 10 ? 10 : maxLength + 2;
        });

        const filename = `Asistencias_${moment().format("YYYY-MM-DD")}`;

        const buffer = await workbook.xlsx.writeBuffer();

        const blob = new Blob([buffer], {
            type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        });

        const link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.download = filename + ".xlsx";
        link.click();
    }

    if ($perSchedule) {
        $perSchedule.onclick = async () => {
            $perSchedule.disabled = true;
            $perSchedule.classList.add("animation-pulse");
            $perSchedule.querySelector("span").innerText = "Espere...";

            try {
                const url = new URL(window.location.href);
                const searchParams = url.searchParams;

                const assists = await window.query(
                    `/api/assists/export?${searchParams.toString()}`
                );

                const groupAssists = assists.reduce((acc, item) => {
                    const { dni, full_name, ...rest } = item;
                    const index = acc.findIndex((i) => i.dni === dni);
                    if (index === -1)
                        acc.push({ dni, full_name, assists: [rest] });
                    else acc[index].assists.push(item);
                    return acc;
                }, []);

                await exportAssists(groupAssists);
            } catch (error) {
                console.error(error);
                window.alert(
                    "Error",
                    "Ocurrió un error al exportar las asistencias"
                );
            } finally {
                $perSchedule.disabled = false;
                $perSchedule.classList.remove("animation-pulse");
                $perSchedule.querySelector("span").innerText = "Exportar";
            }
        };
    }

    if ($centralized) {
        $centralized.onclick = async () => {
            $centralized.disabled = true;
            $centralized.classList.add("animation-pulse");
            $centralized.querySelector("span").innerText = "Espere...";

            try {
                const url = new URL(window.location.href);
                const searchParams = url.searchParams;

                const assists = await window.query(
                    `/api/assists/centralized/export?${searchParams.toString()}`
                );

                const groupAssists = assists.reduce((acc, item) => {
                    const { dni, full_name, ...rest } = item;
                    const index = acc.findIndex((i) => i.dni === dni);
                    if (index === -1)
                        acc.push({ dni, full_name, assists: [rest] });
                    else acc[index].assists.push(item);
                    return acc;
                }, []);

                await exportAssists(groupAssists);
            } catch (error) {
                console.error(error);
                window.alert(
                    "Error",
                    "Ocurrió un error al exportar las asistencias"
                );
            } finally {
                $centralized.disabled = false;
                $centralized.classList.remove("animation-pulse");
                $centralized.querySelector("span").innerText = "Exportar";
            }
        };
    }

    if ($peerUser) {
        $peerUser.onclick = async () => {
            $peerUser.disabled = true;
            $peerUser.classList.add("animation-pulse");
            $peerUser.querySelector("span").innerText = "Espere...";
            const id = $peerUser.getAttribute("data-id");

            try {
                const url = new URL(window.location.href);
                const searchParams = url.searchParams;

                const assists = await window.query(
                    `/api/assists/peer-user/${id}/export?${searchParams.toString()}`
                );

                const groupAssists = assists.reverse().reduce((acc, item) => {
                    const { dni, full_name, ...rest } = item;
                    const index = acc.findIndex((i) => i.dni === dni);
                    if (index === -1)
                        acc.push({ dni, full_name, assists: [rest] });
                    else acc[index].assists.push(item);
                    return acc;
                }, []);

                await exportAssists(groupAssists);
            } catch (error) {
                console.error(error);
                window.alert(
                    "Error",
                    "Ocurrió un error al exportar las asistencias"
                );
            } finally {
                $peerUser.disabled = false;
                $peerUser.classList.remove("animation-pulse");
                $peerUser.querySelector("span").innerText = "Exportar";
            }
        };
    }

    if ($snSchedules) {
        $snSchedules.onclick = async () => {
            $snSchedules.disabled = true;
            $snSchedules.classList.add("animation-pulse");
            $snSchedules.querySelector("span").innerText = "Espere...";

            try {
                const url = new URL(window.location.href);
                const searchParams = url.searchParams;

                const assists = await window.query(
                    `/api/assists/sn-schedules/export?${searchParams.toString()}`
                );

                const workbook = new ExcelJS.Workbook();
                const response = await fetch(
                    "/templates/assists-sn-schedules.xlsx"
                );
                const arrayBuffer = await response.arrayBuffer();

                if (!arrayBuffer) return;

                await workbook.xlsx.load(arrayBuffer);
                const worksheet = workbook.getWorksheet("Asistencias");

                let rr = 4;

                assists.forEach((assist, ii) => {
                    const index = ii + 1 < 10 ? `0${ii + 1}` : ii + 1;
                    const row = worksheet.getRow(rr);
                    rr++;

                    row.getCell(2).value = index;
                    row.getCell(3).value = assist.user.dni ?? "";
                    row.getCell(4).value =
                        assist.user.first_name + " " + assist.user.last_name;
                    row.getCell(5).value = assist.user.role_position.name;
                    row.getCell(6).value =
                        assist.user.role_position.job_position.name;
                    row.getCell(7).value = assist.date;

                    if (assist.date) {
                        row.getCell(7).numFmt = "dd/mm/yyyy";
                    }

                    row.getCell(8).value = assist.day;
                    row.getCell(9).value = assist.terminal.name;
                    row.getCell(10).value = assist.time;
                    row.getCell(10).numFmt = "hh:mm:ss";
                    row.getCell(11).value = assist.sync_date;
                });

                worksheet.columns.forEach((column) => {
                    let maxLength = 0;
                    column.eachCell({ includeEmpty: true }, (cell) => {
                        const cellValue = cell.value
                            ? cell.value.toString()
                            : "";
                        maxLength = Math.max(maxLength, cellValue.length);
                    });
                    column.width = maxLength < 10 ? 10 : maxLength + 2;
                });

                const filename = `Asistencias sin calcular ${moment().format(
                    "YYYY-MM-DD"
                )}`;

                const buffer = await workbook.xlsx.writeBuffer();

                const blob = new Blob([buffer], {
                    type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                });

                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = filename + ".xlsx";
                link.click();
            } catch (error) {
                console.error(error);
                window.alert(
                    "Error",
                    "Ocurrió un error al exportar las asistencias"
                );
            } finally {
                $snSchedules.disabled = false;
                $snSchedules.classList.remove("animation-pulse");
                $snSchedules.querySelector("span").innerText = "Exportar";
            }
        };
    }

    if ($withoutCalculating) {
        $withoutCalculating.onclick = async () => {
            $withoutCalculating.disabled = true;
            $withoutCalculating.classList.add("animation-pulse");
            $withoutCalculating.querySelector("span").innerText = "Espere...";

            try {
                const url = new URL(window.location.href);
                const searchParams = url.searchParams;

                const assists = await window.query(
                    `/api/assists/without-calculating/export?${searchParams.toString()}`
                );

                const workbook = new ExcelJS.Workbook();
                const response = await fetch(
                    "/templates/without-calculating.xlsx"
                );
                const arrayBuffer = await response.arrayBuffer();

                if (!arrayBuffer) return;

                await workbook.xlsx.load(arrayBuffer);
                const worksheet = workbook.getWorksheet("Asistencias");

                let rr = 4;

                assists.forEach((assist, ii) => {
                    const index = ii + 1 < 10 ? `0${ii + 1}` : ii + 1;
                    const row = worksheet.getRow(rr);
                    rr++;

                    row.getCell(2).value = index;
                    row.getCell(3).value = assist.user.emp_code ?? "";
                    row.getCell(4).value =
                        assist.user.first_name + " " + assist.user.last_name;
                    row.getCell(5).value = assist.date;

                    if (assist.date) {
                        row.getCell(5).numFmt = "dd/mm/yyyy";
                    }

                    row.getCell(6).value = assist.day;
                    row.getCell(7).value = assist.terminal.name;
                    row.getCell(8).value = assist.time;
                    row.getCell(8).numFmt = "hh:mm:ss";
                    row.getCell(9).value = assist.sync_date;
                });

                worksheet.columns.forEach((column) => {
                    let maxLength = 0;
                    column.eachCell({ includeEmpty: true }, (cell) => {
                        const cellValue = cell.value
                            ? cell.value.toString()
                            : "";
                        maxLength = Math.max(maxLength, cellValue.length);
                    });
                    column.width = maxLength < 10 ? 10 : maxLength + 2;
                });

                const filename = `Asistencias sin calcular ${moment().format(
                    "YYYY-MM-DD"
                )}`;

                const buffer = await workbook.xlsx.writeBuffer();

                const blob = new Blob([buffer], {
                    type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                });

                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = filename + ".xlsx";
                link.click();
            } catch (error) {
                console.error(error);
                window.alert(
                    "Error",
                    "Ocurrió un error al exportar las asistencias"
                );
            } finally {
                $withoutCalculating.disabled = false;
                $withoutCalculating.classList.remove("animation-pulse");
                $withoutCalculating.querySelector("span").innerText =
                    "Exportar";
            }
        };
    }
});
