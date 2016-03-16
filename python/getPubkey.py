#!/usr/bin/env python
#encoding:utf-8
import re
import rsa
import time
import json
import urllib
import base64
import random
import binascii
import sys


pubkey = sys.argv[1]
servertime = sys.argv[2]
nonce = sys.argv[3]
pwd = sys.argv[4]
key = rsa.PublicKey(int(pubkey, 16), 65537)
st  = str(servertime) + '\t' + nonce + '\n' + pwd
sp  = rsa.encrypt(st.encode('utf-8'), key)
sp  = binascii.b2a_hex(sp)   
print(sp)



