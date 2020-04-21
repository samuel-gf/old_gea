# Changelog Gea

## Ideas
- Iniciativa automática: Reordena la lista de retratos que hay a la derecha
- Contador de acciones, se resetea cuando todos hayan realizado todas
- Modos de combate: carga, defensiva...

## Versiones

1.10	Los PJs deben seleccionar el guideline a utilizar por anticipado
1.09	El explorador tiene +2 al atacar a pieles verde a distancia
1.08	El explorador tiene +2 al atacar a pieles verdes en cuerpo a cuerpo
		Array
		(
			[idBoard] => 1
			[tokenName] => exp
			[guideNumber] => 1
			[name] => Espada larga 1d8+2
			[guideAction] => flmde_attack_enemy r1 a1d8+2 d1d12+2
			[n] => -1
			[maxn] => -1
		)
1.07	Simplificar _guidelines_ de _lmde_
1.06	Fichas de personaje mejoradas
1.05	Las fichas muestran los datos dinámicos
		Las fichas tienen campos dinámicos: _t:name_ representa el Nombre del token, mientras que
		_a:hp_ representa el Atributo hp, por otra parte _g:name:1_ representa el nombre del
		guideline numerado como 1
1.04	Las armas de distancia pierden efectividad con la distancia
1.03	Escenario de la caravana completo
1.02	Cargador de tokens en la base de datos
1.01	Los *guidelines* se leen y se ejecutan en el servidor, no en el cliente
		guideline: fattack r1 a1d20+5 d1d12+2	(Function Range1, Attack 1d20+5, Damage1d12+2)
		sendCommand: gNumberOfGuideline
1.0 	Primera versión testeada a principios de abril de 2020 con Rafael Alonso y José Fernández