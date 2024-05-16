Send ping to verify communication from gatekeeper to kOS.
```shell

export AUTH_KEY="<enter key visible upon creation of a new gatekeeper>"
export KOS_HOST="localhost:8000"

curl \
    -d "auth_key=${AUTH_KEY}" \
    -d 'endpoint=ping' \
    "http://${KOS_HOST}/keys/"

```

```json
{
  "code": "0",
  "text": "PING? PONG!",
  "timestamp": "2024-05-15 04:02:59"
}
```


Get keys per gatekeeper from kOS.
```shell

export AUTH_KEY="<enter key visible upon creation of a new gatekeeper>"
export KOS_HOST="localhost:8000"

curl \
    -d "auth_key=$AUTH_KEY" \
    -d 'endpoint=get_keys' \
    "http://${KOS_HOST}/keys/"

```

```json
{
  "code": "0",
  "text": "OK",
  "timestamp": "2024-05-15 04:02:08",
  "payload": [
    "b20721d9faa590fb63f8dda796e751ba",
    "328c822485d8d8920281afe99bf9fd54",
    "b0793ef542870bdbda2ebb771735e184",
    "a7b0927e74a38688185094a0f8e67f3b",
    "4d2dbd64091147791d173f6e07e42ac7",
    "bbedf2bd164fb569718b0a7348cadb65",
    "b0698de32d6a0e6ebccbe6bbd23d2cf4",
    "1f9bcca9f7eb9bb17ff817adb0c83e95",
    "971c26dda12b449ab858bdf4f536a59f",
    "a24eefba02586c439e7467c4de8527a9",
    "5550c13bdba1f6b3224ecf00ca6d5e3c",
    "5de1f964bd3f37ef892c172ce6d91273",
    "b3e5059b4fcd5c53c3ea64baf04582a5",
    "bd102ab4a45d23203d002e26730c8df3",
    "3a5a7cdfde25c0f94f32640c578a61e1",
    "525cd95dc6cb5a86811139e1124d005c",
    "8c14a9fd6b715a79ba71ca7838524c36",
    "214fe402220570f1b6b334f72a158574",
    "1f100ae6e91f21698599f5f3af52bccf",
    "157141a6231cf55200323ecb7ee282d2",
    "26a39f0f47d0ab5baf9f6bd46d7a6d62",
    "c688fd8983f558feae329e3a239d5126",
    "60e4067154c4e2f116122925370da764",
    "ef35288585937c0cde7c8034cdb85f4e",
    "01de65d224454ffac14c0fe65c96d517",
    "5cd645daa720e58a5e552ee8fcd25c9b",
    "f10ee0c810ce8414558a0de76517855c",
    "454329beda5c9f80c96aa00298defe18",
    "f573051ae00cb5633d7003624e4dd111",
    "7097a176c0fd42a598135dcadb7768e3",
    "be7b0f30ab8d71129ec139d596e92f89",
    "078bb15cb04c62a8002e7b1f93e23de6",
    "6f246cd08edc8748e7b863e6a58eac8f",
    "0db22925833e19274b7968e17ee29f06",
    "d405af37210779c543d7d1a029605449",
    "f52eaa6a7abf24e1c57774476203ee4e",
    "ea4ce15438e5ca9adcd9b7be2af54f5c",
    "94749112fd4518ab560a45958349fcb6",
    "560fc3d1835e2e3b47055da742d56765",
    "f723782a0375b42ae85183d67351d443",
    "de303d0f0a515f288e57ee465de406f0",
    "bc2d65d1ea5e77c50414c91454b8a24f",
    "ad6d259dad66c052c8135056c047a239",
    "6004472fd9e39e29fb18925f1e8535cb",
    "8692dc497562cecc29d711aeaf8114a0",
    "a1950cc2a2bd259bc2d19ed44cd6fe64",
    "59dd0e906f1fa8e5421f2ebe79eb21c2",
    "5a66e12a44b5eea4e61c3ebc76d33903",
    "bca0a29aa4a9addf5464111e55e88a17",
    "bd976c9690388245bfed4e135cf861de",
    "1c7b4326899c055917960ad162c3208c",
    "28eee9c4bad46f3940cb53db17a0bacb",
    "b783f531672b22c75734fa5276c809a2",
    "efe47afefca09847b8d1a33dfe6568c1"
  ]
}
```

Send local authentications up to kOS.

```shell
export AUTH_KEY="<enter key visible upon creation of a new gatekeeper>"
export KOS_HOST="localhost:8000"
export ENDPOINT="send_auths"
export PAYLOAD=$(cat <<EOF
[
  {
    "rfid": "b20721d9faa590fb63f8dda796e751ba",
    "result": "allow",
    "metadata": "",
    "lock_in": "2024-5-14",
    "lock_out": "2024-5-14",
    "created_at": "2024-5-14"
  }
]
EOF
)

curl \
    -d "auth_key=${AUTH_KEY}" \
    -d "endpoint=${ENDPOINT}" \
    -d "payload=${PAYLOAD}" \
    "http://${KOS_HOST}/keys/"
```

```json
{
  "code": "0",
  "text": "OK",
  "timestamp": "2024-05-15 23:00:00"
}
```



```shell
export AUTH_KEY="<enter key visible upon creation of a new gatekeeper>"
export KOS_HOST="localhost:8000"
export ENDPOINT="get_keys"
export STATUS=$(cat <<EOF
{
  "status": "online",
  "user_rfid": "0",
  "user_lock_in": ""
}
EOF
)

curl \
    -d "auth_key=${AUTH_KEY}" \
    -d "endpoint=${ENDPOINT}" \
    -d "status=${STATUS}" \
    "http://${KOS_HOST}/keys/"
```
