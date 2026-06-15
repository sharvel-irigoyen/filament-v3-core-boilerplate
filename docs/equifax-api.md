# Equifax API — Documentación de Respuesta

Documentación de la estructura de respuesta del API de Equifax (Infocorp Empresarial Plus).

## Endpoint

Consulta de reporte crediticio por documento (RUC/DNI).

### Request

| Campo           | Tipo   | Descripción                                      |
|-----------------|--------|--------------------------------------------------|
| `id`            | string | Número de documento (RUC o DNI)                  |
| `tipoPersona`   | string | `"1"` = Persona Jurídica, `"2"` = Persona Natural |
| `tipoDocumento` | string | `"6"` = RUC, `"1"` = DNI                         |

### Response — Estructura General

```
{
  "status": {
    "success": bool,
    "statusCode": int,       // HTTP status
    "statusText": string
  },
  "usermessage": string,
  "data": {
    "applicants": {
      "primaryConsumer": {
        "personalInformation": { ... },
        "interconnectResponse": [ ...bloques... ]
      }
    }
  }
}
```

Cada bloque en `interconnectResponse` tiene:

| Campo          | Tipo   | Descripción                                  |
|----------------|--------|----------------------------------------------|
| `Codigo`       | int    | Identificador del bloque (ver tabla abajo)   |
| `Nombre`       | string | Nombre descriptivo del bloque                |
| `TieneError`   | bool   | Solo en código 100                           |
| `DetallesError` | object | Detalles del error (vacío si no hay error)  |
| `Data`         | object | Payload del bloque (estructura varía)        |
| `Data.flag`    | bool   | `true` si hay datos disponibles              |

---

## Bloques de Respuesta (por Código)

### Código 100 — Resumen Consulta

Metadatos de la consulta. No contiene `Data`.

```json
{
  "Codigo": 100,
  "Nombre": "Resumen Consulta",
  "fechaHora": "11/05/2026 13:44:28",
  "zonaHoraria": "America/Lima",
  "TieneError": false,
  "DetallesError": {}
}
```

---

### Código 865 — Resumen Flag

Resumen ejecutivo del perfil crediticio. Contiene dos sub-bloques:

#### `ResumenComportamiento`

| Campo                       | Tipo    | Ejemplo              |
|-----------------------------|---------|----------------------|
| `TipoDocumento`             | string  | `"6"`                |
| `NumeroDocumento`           | string  | `"20608282221"`      |

##### `ResumenDeuda`

| Campo                       | Tipo    | Descripción                          |
|-----------------------------|---------|--------------------------------------|
| `Periodo`                   | string  | `"Marzo 2026"`                       |
| `DeudaTotal`                | float   | Deuda total en soles                 |
| `DeudaDirecta`              | float   | Deuda directa                        |
| `DeudaIndirecta`            | float   | Deuda indirecta (avales, fianzas)    |
| `PorcentajeDeudaNormal`     | int     | % calificación Normal                |
| `PorcentajeDeudaPotencial`  | int     | % calificación CPP                   |
| `PorcentajeDeudaDeficiente` | int     | % calificación Deficiente            |
| `PorcentajeDeudaEnRiesgo`   | int     | % calificación Dudoso                |
| `PorcentajeDeudaPerdida`    | int     | % calificación Pérdida               |
| `Variacion`                 | int     | Variación porcentual vs periodo anterior |

##### `ResumenScoreHistorico`

Contiene tres objetos con la misma estructura: `ScoreActual`, `ScoreAnterior`, `ScoreHace12Meses`.

| Campo            | Tipo    | Valores posibles                                    |
|------------------|---------|-----------------------------------------------------|
| `Periodo`        | string  | `"Mayo 2026"`                                       |
| `Riesgo`         | string  | `"MUY ALTO"`, `"ALTO"`, `"MEDIO"`, `"BAJO"`, `"MUY BAJO"` |
| `MotivoSinScore` | string? | Razón si no se pudo calcular score (null si existe) |

#### `ResumenBloqueFlags`

Indicadores binarios del perfil. Valores: `"SI"`, `"NO"`, `"-"` (no aplica), `null`.

| Campo                      | Tipo    | Descripción                           |
|----------------------------|---------|---------------------------------------|
| `TarjetaCredito`           | string  | Tiene tarjeta de crédito              |
| `LineaDeCredito`           | string? | Tiene línea de crédito                |
| `CreditoHipotecario`      | string  | Tiene crédito hipotecario             |
| `BuenPagadorDeServicios`   | string  | Buen pagador de servicios             |
| `EstaEnInfocorp`           | string  | Reportado en Infocorp                 |
| `AvalAvalado`              | string  | Es aval o avalado                     |
| `RepresentanteLegal`       | string  | Es representante legal                |
| `GastoMensualEstimado`     | float   | Gasto mensual estimado (soles)        |
| `PosibleRestringido`       | string  | Posible restringido                   |
| `TieneAuto`                | string  | Tiene vehículo                        |
| `EntidadesQueConsultaron`  | int     | Nro. de entidades que consultaron     |
| `Homonimos`                | string  | Cantidad de homónimos                 |
| `ComercioExterior`         | string? | Actividad de comercio exterior        |
| `DeudaPrevisional`         | string? | Deuda previsional (AFP)               |
| `AlertaPep`                | string  | Alerta PEP (Persona Expuesta Políticamente) |
| `AlertaRedam`              | string  | Alerta REDAM (deudor alimentario)     |
| `ReactivaPeru`             | string  | Beneficiario Reactiva Perú            |
| `EsInquilinoMoroso`        | string  | Es inquilino moroso                   |
| `ReactivaPeruInfo`         | object  | `{ Fecha, Monto }` — detalle Reactiva|

---

### Código 822 — Score Predictivo con Variables

Score de riesgo crediticio con las variables que lo componen.

#### `ResumenScoreRP3`

| Campo                | Tipo   | Descripción                              |
|----------------------|--------|------------------------------------------|
| `Puntaje`            | int    | Score numérico (0–999)                   |
| `NivelRiesgo`        | string | `"MUY ALTO"`, `"ALTO"`, `"MEDIO"`, `"BAJO"`, `"MUY BAJO"` |
| `Conclusion`         | string | Texto explicativo del nivel de riesgo    |
| `PrincipalesVariables` | object | Variables que influyen en el score     |

#### `PrincipalesVariables`

Objeto con claves descriptivas. Cada variable:

| Campo      | Tipo   | Descripción                                |
|------------|--------|--------------------------------------------|
| `Variable` | string | Descripción de la variable evaluada        |
| `Valor`    | string | `"SI"` o `"NO"`                            |

Claves conocidas:
- `PresentaInformacionSFR` — Info en sistema financiero regulado sin >30 días atraso (12 meses)
- `PresentaDeudas` — Deuda castigada, refinanciada o judicial (último año)
- `InformacionReportadaInfocorp` — Info negativa en Infocorp/SICOM (último año)
- `MalComportamientoCrediticio` — Mal comportamiento crediticio (último año)

---

### Código 861 — Comportamiento General

Histórico de comportamiento crediticio (últimos 13 meses).

#### `SistemaFinanciero.SFDetalle[]`

| Campo              | Tipo   | Descripción                     |
|--------------------|--------|---------------------------------|
| `Periodo`          | string | `"Mar 2026"` (formato abreviado)|
| `DiasAtrasoMinimo` | int    | Días de atraso mínimo           |
| `DiasAtrasoMaximo` | int    | Días de atraso máximo           |
| `Riesgo`           | string | Nivel de riesgo del periodo     |

#### `OtrasDeudasImpagas.ODIDetalle[]`

| Campo                        | Tipo   | Descripción                    |
|------------------------------|--------|--------------------------------|
| `Periodo`                    | string | Mes/año                        |
| `Riesgo`                     | string | Nivel de riesgo                |
| `DeudasImpagasProtestadas`   | object | `{ MontoEnSoles, MontoEnDolares }` |
| `DeudasImpagasInfocorp`      | object | `{ MontoEnSoles, MontoEnDolares }` |
| `DeudasImpagasSunat`         | object | `{ MontoEnSoles, MontoEnDolares }` (puede estar vacío) |
| `DeudasImpagasPrevisional`   | object | `{ MontoEnSoles, MontoEnDolares }` (puede estar vacío) |

> **Nota:** Los montos en `MontoEnSoles` y `MontoEnDolares` vienen como **strings** (no floats).

---

### Código 859 — Resumen Financiero (SBS y No Regulado)

Deudas en el sistema financiero con comparación a periodos anteriores.

#### `ResumenFinanciero.DeudasUltimoPeriodo`

| Campo          | Tipo   | Ejemplo    |
|----------------|--------|------------|
| `periodo`      | string | `"202603"` |
| `MesActual`    | string | `"202603"` |
| `MesAnterior`  | string | `"202602"` |
| `AnioAnterior` | string | `"202503"` |

#### `Deudas[]`

| Campo                         | Tipo   | Descripción                           |
|-------------------------------|--------|---------------------------------------|
| `Entidad`                     | string | Nombre de la entidad financiera       |
| `SistemaFinanciero`           | string | `"SR"` (regulado), `"SNR"` (no regulado) |
| `CalificacionMesActual`       | string | `"NOR"`, `"CPP"`, `"DEF"`, `"DUD"`, `"PER"` |
| `MontoSubTotalMesActual`      | float  | Deuda mes actual                      |
| `CalificacionMesAnterior`     | string | Calificación mes anterior             |
| `MontoSubTotalMesAnterior`    | float  | Deuda mes anterior                    |
| `CalificacionAnioAnterior`    | string | Calificación hace 12 meses            |
| `MontoSubTotalAnioAnterior`   | float  | Deuda hace 12 meses                   |

##### `Productos[]` (dentro de cada deuda)

| Campo              | Tipo   | Descripción                              |
|--------------------|--------|------------------------------------------|
| `Tipo`             | string | Código del producto (ej: `"SOBCTACTE"`)  |
| `Descripcion`      | string | Nombre del producto                      |
| `MontoMesActual`   | float  | Monto mes actual                         |
| `MontoMesAnterior` | float  | Monto mes anterior                       |
| `MontoAnioAnterior`| float  | Monto hace 12 meses                      |
| `DiasAtraso`       | string | Días de atraso (viene como string)       |

#### Calificaciones SBS

| Código | Significado         |
|--------|---------------------|
| `NOR`  | Normal              |
| `CPP`  | Con Problemas Potenciales |
| `DEF`  | Deficiente          |
| `DUD`  | Dudoso              |
| `PER`  | Pérdida             |

#### `Totales`

```json
{
  "MontoTotalMesActual": 5329.95,
  "MontoTotalMesAnterior": 4451.31,
  "MontoTotalAnioAnterior": 1460.09
}
```

---

### Código 857 — Otras Deudas Impagas: Resumen

Deudas reportadas fuera del sistema financiero regulado.

#### Sub-bloques

| Sub-bloque           | Descripción                                  |
|----------------------|----------------------------------------------|
| `Sicom`              | Deudas reportadas a Infocorp (SICOM)         |
| `NegativoSunat`      | Deudas negativas con SUNAT                   |
| `Omisos`             | Declaraciones omisas ante SUNAT              |
| `Protestos`          | Letras/pagarés protestados                   |
| `CuentasCerradas`    | Cuentas cerradas por entidades financieras   |
| `TarjetasAnuladas`   | Tarjetas de crédito anuladas                 |
| `Redam`              | Registro de Deudores Alimentarios Morosos    |
| `InquilinosMorosos`  | Inquilinos morosos reportados                |

> Cada sub-bloque tiene `Cabecera` y `Detalle`. Cuando no hay datos, ambos son objetos vacíos `{}`.

`Protestos` tiene estructura especial: `ProtestosAclarados` y `ProtestosNoAclarados`, cada uno con su `Cabecera` y `Detalle`.

Los demás sub-bloques comparten la misma estructura:

##### `Cabecera`

| Campo                     | Tipo   | Descripción                     |
|---------------------------|--------|---------------------------------|
| `FechaVencimientoReciente`| string | `"dd/mm/yyyy"`                  |
| `CantidadSoles`           | int    | Cantidad de deudas en soles     |
| `MontoSoles`              | float  | Monto total en soles            |
| `CantidadDolares`         | int    | Cantidad de deudas en dólares   |
| `MontoDolares`            | float  | Monto total en dólares          |

##### `Detalle.Deuda[]`

| Campo               | Tipo   | Descripción                                |
|----------------------|--------|--------------------------------------------|
| `FechaVencimiento`   | string | `"dd/mm/yyyy"`                             |
| `FechaReportada`     | string | `"dd/mm/yyyy"`                             |
| `Divisa`             | string | `"S"` = Soles, `"D"` = Dólares            |
| `Monto`              | float  | Monto de la deuda                          |
| `Acreedor`           | string | Nombre del acreedor                        |
| `DocumentoBancario`  | string | Tipo doc (ver tabla abajo)                 |
| `CondicionDeuda`     | string | `"MOROSA"`, `"VENCIDA"`, etc.              |
| `TipoDeudor`         | string | `"D"` = Deudor directo                     |
| `GiroNegocio`        | string | Giro de negocio (puede estar vacío)        |

##### Tipos de `DocumentoBancario`

| Código | Significado              |
|--------|--------------------------|
| `FAC`  | Factura                  |
| `REC`  | Recibo                   |
| `RS`   | Resolución de sanción    |
| `LT`   | Letra                    |
| `""`   | No especificado          |

Para `Protestos`, campos adicionales:
| Campo       | Tipo   | Descripción         |
|-------------|--------|---------------------|
| `Aceptante` | string | Nombre del aceptante|
| `Girador`   | string | Nombre del girador  |

---

### Código 602 — Directorio de Personas

Directorio de personas vinculadas al documento consultado.

```json
{
  "Codigo": 602,
  "Nombre": "DIRECTORIO DE PERSONAS",
  "Data": {
    "flag": false,
    "DirectorioPersona": {}
  }
}
```

> Cuando `flag` es `false`, `DirectorioPersona` es un objeto vacío. Cuando `true`, contiene datos de las personas asociadas.

---

### Código 853 — Direcciones Registradas

Direcciones asociadas al documento, de fuentes externas y verificadas.

#### `Direcciones.FuentesExternas.Direccion[]`

| Campo       | Tipo    | Descripción                                    |
|-------------|---------|------------------------------------------------|
| `Numero`    | int     | Número de orden                                |
| `Fecha`     | string  | `"dd/mm/yyyy"` — fecha de registro             |
| `Direccion` | string  | Dirección completa                             |
| `Ubigeo`    | string  | `"Distrito - Provincia - Departamento"`        |
| `Telefono`  | string? | Teléfono asociado (puede ser `null`)           |
| `Anexo`     | string? | Anexo telefónico (puede ser `null`)            |
| `Fuente`    | string  | Fuente del dato (ej: `"SUNAT"`)               |
| `Tipo`      | string  | Tipo de fuente (ej: `"SUNAT"`)                |

#### `Direcciones.Verificadas`

Mismo esquema que `FuentesExternas`. Objeto vacío `{}` si no hay direcciones verificadas.

---

### Código 877 — Directorio SUNAT

Información tributaria del contribuyente desde SUNAT.

#### `DirectorioSUNAT.Directorio[]`

| Campo                      | Tipo    | Descripción                                    |
|----------------------------|---------|------------------------------------------------|
| `RUC`                      | string  | Número de RUC                                  |
| `RazonSocial`              | string  | Razón social                                   |
| `NombreComercial`          | string? | Nombre comercial (puede ser `null`)            |
| `TipoContribuyente`        | string  | Ej: `"SOCIEDAD ANONIMA"`, `"PERSONA NATURAL"`  |
| `EstadoContribuyente`      | string  | `"ACTIVO"`, `"BAJA DEFINITIVA"`, etc.          |
| `CondicionContribuyente`   | string  | `"HABIDO"`, `"NO HABIDO"`, etc.                |
| `Dependencia`              | string  | Oficina SUNAT (ej: `"I.R.LIMA-MEPECO"`)       |
| `CodigoCIIU`               | int     | Código CIIU de actividad económica             |
| `DescripcionCIIU`          | string  | Descripción de la actividad CIIU               |
| `InicioActividades`        | string  | `"dd/mm/yyyy"` — fecha de inicio               |
| `ActividadComercioExterior`| string  | `"SIN ACTIVIDAD"`, `"IMPORTADOR"`, etc.        |
| `Direcciones.Direccion`    | string  | Dirección fiscal registrada en SUNAT           |
| `NumeroTrabajadores`       | string  | Cantidad de trabajadores (vacío si no reporta) |
| `AniosEnElMercado`         | string  | Años de antigüedad en el mercado               |
| `CantidadAnexos`           | string  | Cantidad de establecimientos anexos             |

---

### Código 855 — Representantes Legales

Personas que representan a la empresa y empresas donde la persona es representante.

#### `RepresentantesLegales.RepresentadosPor[]`

Personas que representan a la entidad consultada.

| Campo                | Tipo   | Descripción                              |
|----------------------|--------|------------------------------------------|
| `TipoDocumento`      | string | `"1"` = DNI, `"3"` = Carnet Extranjería |
| `NumeroDocumento`    | string | Número de documento (con ceros a la izquierda) |
| `FechaInicioCargo`   | string | `"dd/mm/yyyy"`                           |
| `Cargo`              | string | Ej: `"APODERADO"`, `"GERENTE GENERAL"`  |
| `Nombre`             | string | Nombre completo (APELLIDO APELLIDO NOMBRE) |
| `ScoreHistoricos`    | object | Score crediticio del representante       |

##### `ScoreHistoricos`

Contiene `ScoreActual`, `ScoreAnterior`, `ScoreHace12Meses` (misma estructura que en código 865):

| Campo     | Tipo   | Descripción                                          |
|-----------|--------|------------------------------------------------------|
| `Periodo` | string | `"Mes yyyy"` (ej: `"Febrero 2026"`)                 |
| `Riesgo`  | string | `"RIESGO MUY BAJO"`, `"RIESGO BAJO"`, `"RIESGO MEDIO"`, `"RIESGO ALTO"`, `"RIESGO MUY ALTO"` |

> **Nota:** Los valores de `Riesgo` en este bloque usan el prefijo `"RIESGO "` (ej: `"RIESGO MUY BAJO"`), a diferencia del código 865 donde son solo `"MUY BAJO"`.

#### `RepresentantesLegales.RepresentantesDe.RepresentantesDe[]`

Empresas donde la entidad consultada actúa como representante. Misma estructura que `RepresentadosPor[]`, con campos del representado en lugar del representante.

---

### Código 875 — Empresas Relacionadas

```json
{
  "Codigo": 875,
  "Nombre": "EMPRESAS RELACIONADAS",
  "Data": {
    "flag": false,
    "EmpresasRelacionadas": {}
  }
}
```

---

### Código 869 — Avalistas

```json
{
  "Codigo": 869,
  "Nombre": "AVALISTAS",
  "Data": {
    "flag": false,
    "Avalistas": {}
  }
}
```

---

### Código 870 — Avalados

```json
{
  "Codigo": 870,
  "Nombre": "AVALADOS",
  "Data": {
    "flag": false,
    "Avalados": {}
  }
}
```

---

### Código 873 — Sistema Financiero Regulado y No Regulado

Detalle histórico del comportamiento de pago y deudas en el sistema financiero.

#### `SistemaFinanciero.ResumenComportamientoPago.Semaforo[]`

| Campo                        | Tipo   | Descripción                               |
|------------------------------|--------|-------------------------------------------|
| `periodo`                    | string | `"YYYYMM"` (ej: `"202503"`)               |
| `NoTieneImpagos`             | bool   | `true` si no hay impagos                  |
| `TieneDeudasAtrasadas`       | bool   | `true` si hay deudas con atraso           |
| `TieneDeudasImpagasInfocorp` | bool   | `true` si reporta impagos en Infocorp     |
| `InformacionNoDisponible`    | bool   | `true` si la información no está completa |
| `DiasAtraso`                 | int    | Días de atraso en el periodo              |

#### `SistemaFinanciero.DeudasHistoricas.Deuda[]`

| Campo                 | Tipo   | Descripción                                   |
|-----------------------|--------|-----------------------------------------------|
| `periodo`             | string | `"YYYYMM"`                                    |
| `Calificacion`        | string | `"NOR"`, `"CPP"`, `"DEF"`, `"DUD"`, `"PER"`, `""`|
| `Porcentaje`          | string | Porcentaje de la calificación (ej: `"100.0"`) |
| `NroEntidades`        | int    | Cantidad de entidades financieras             |
| `DeudaVigente`        | int    | Monto/indicador de deuda vigente              |
| `DeudaAtrasada`       | int    | Monto/indicador de deuda atrasada             |
| `DeudaVencida`        | int    | Monto/indicador de deuda vencida              |
| `DeudaRefinanciada`   | int    | Monto/indicador de deuda refinanciada         |
| `DeudaReestructurada` | int    | Monto/indicador de deuda reestructurada       |
| `DeudaJudicial`       | int    | Monto/indicador de deuda judicial             |
| `DeudaCastigada`      | int    | Monto/indicador de deuda castigada            |
| `DeudaTotal`          | int    | Monto/indicador de deuda total                |
| `DiasAtraso`          | int    | Días de atraso en este periodo                |

---

### Código 863 — Registro Crediticio Consolidado (RCC)

Detalle mensual de cuentas y entidades del sistema financiero.

#### `RegistroCrediticioConsolidado.RCC.Periodos.Periodo[]`

| Campo            | Tipo   | Descripción                               |
|------------------|--------|-------------------------------------------|
| `valor`          | string | `"YYYYMM"`                                |
| `flag`           | bool   | `true` si hay información en el mes       |
| `NroEntidades`   | string | Cantidad de entidades (como string)       |
| `Calificaciones` | object | Distribución en % (`{ "NOR": "0", ... }`) |

##### `Deudas.Deuda[]` (dentro de cada periodo)

| Campo               | Tipo   | Descripción                            |
|---------------------|--------|----------------------------------------|
| `CodigoCuenta`      | string | Código contable SBS                    |
| `NombreCuenta`      | string | Código corto del producto              |
| `DescripcionCuenta` | string | Descripción de la cuenta               |
| `CodigoEntidad`     | string | Código de la entidad financiera        |
| `NombreEntidad`     | string | Nombre del banco / caja                |
| `Calificacion`      | string | `"NOR"`, `"PER"`, etc.                 |
| `Monto`             | float  | Monto de la deuda                      |
| `Moneda`            | string | `"S"` (Soles) o `"D"` (Dólares)        |

---

### Código 880 — Resumen de Comportamiento de Pago

Semáforo de deudas impagas por periodo.

#### `DeudasImpagas.ResumenDeudasImpagas.SemaforoPeriodo[]`

| Campo              | Tipo   | Descripción                               |
|--------------------|--------|-------------------------------------------|
| `periodo`          | string | `"YYYYMM"`                                |
| `TieneDeuda`       | bool   | Indica si existe deuda impaga en el mes   |

##### `DetalleProductos.ProductoDeuda[]`

| Campo            | Tipo   | Descripción                             |
|------------------|--------|-----------------------------------------|
| `CodigoProducto` | string | Código del tipo de producto             |
| `DeudaSoles`     | float  | Monto de la deuda en soles              |
| `DeudaDolares`   | float  | Monto de la deuda en dólares            |

---

## Notas Generales

1. **Fechas**: Se usan dos formatos — `"dd/mm/yyyy"` en deudas y `"Mes yyyy"` en resúmenes.
2. **Montos como strings**: En `ODIDetalle` los montos vienen como strings. En otros bloques vienen como floats.
3. **Campos nullable**: Muchos campos pueden ser `null`, `"-"`, o un objeto vacío `{}` cuando no aplican.
4. **`Data.flag`**: Siempre verificar que sea `true` antes de procesar el contenido del bloque.
5. **Periodos**: El formato varía — `"202603"` (YYYYMM), `"Mar 2026"`, `"Marzo 2026"` según el bloque.
