echo STARTING VLNA
FOR /R %%G IN (*.tex) DO (
	vlna32.exe -l -m -n %%G 
)
echo ENDING VLNA
echo STARTING DELETE TEMP FILES
FOR /R %%G IN (*.te~) DO (
	DEL %%G
)
echo ENDING DELETE TEMP FILES
