import banorte from txtbanorteAtxtMicrosip

#procesa archivo de entrada
# output: []
def procesaEntrada(archivo):
    pass

# output: salida
def procesaSalida(archivo):
    pass


if __name__ == '__main__':
    operacion = ""
    banco = "banorte"
    entrada = "smartbanorte5595enero.txt"
    salida = "smartProcesado.txt"

    datos = procesaEntrada(entrada)
    if banco == "banorte":
        if datos != null:
            banorte(datos)
    else if banco == "santander":
        pass
