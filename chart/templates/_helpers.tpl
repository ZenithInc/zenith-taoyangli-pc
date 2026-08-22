{{- define "pc.name" -}}pc{{- end }}

{{- define "pc.fullname" -}}
{{- .Release.Name | trunc 63 | trimSuffix "-" -}}
{{- end }}

{{- define "pc.selectorLabels" -}}
app.kubernetes.io/name: {{ include "pc.name" . }}
app.kubernetes.io/instance: {{ .Release.Name }}
{{- end }}

{{- define "pc.labels" -}}
helm.sh/chart: {{ printf "%s-%s" .Chart.Name .Chart.Version | replace "+" "_" | quote }}
{{ include "pc.selectorLabels" . }}
app.kubernetes.io/managed-by: {{ .Release.Service }}
{{- end }}

{{- define "pc.image" -}}
{{- $repository := required "image.repository is required" .Values.image.repository -}}
{{- if ne $repository "registry.taoyangli.cn/taoyangli/pc" -}}{{- fail "unexpected image.repository" -}}{{- end -}}
{{- $tag := required "image.tag must be a full Git SHA" .Values.image.tag -}}
{{- if not (regexMatch "^[0-9a-f]{40}$" $tag) -}}{{- fail "image.tag must be a full lowercase Git SHA" -}}{{- end -}}
{{- $digest := required "image.digest is required" .Values.image.digest -}}
{{- if not (regexMatch "^sha256:[0-9a-f]{64}$" $digest) -}}{{- fail "image.digest must be a sha256 digest" -}}{{- end -}}
{{- printf "%s:%s@%s" $repository $tag $digest -}}
{{- end }}

{{- define "pc.runtimeSecret" -}}
{{- $name := required "runtimeSecretName is required" .Values.runtimeSecretName -}}
{{- if not (regexMatch "^pc-env-[0-9a-f]{12}$" $name) -}}{{- fail "runtimeSecretName must use the content-digest suffix" -}}{{- end -}}
{{- $name -}}
{{- end }}
