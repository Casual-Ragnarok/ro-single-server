@ECHO OFF
reg delete "HKCU\Software\Classes\{49064D4F-D3C0-8818-C173-74BE82606519}" /f

echo "[OK] Reset Try Time"
pause