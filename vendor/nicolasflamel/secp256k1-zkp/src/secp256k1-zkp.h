// Header guard
#ifndef SECP256K1_ZKP_H
#define SECP256K1_ZKP_H


// Constants

// Private key size
extern __attribute__((dllexport, visibility("default"))) const size_t PRIVATE_KEY_SIZE;

// Public key size
extern __attribute__((dllexport, visibility("default"))) const size_t PUBLIC_KEY_SIZE;

// Blinding factor size
extern __attribute__((dllexport, visibility("default"))) const size_t BLINDING_FACTOR_SIZE;

// Commitment size
extern __attribute__((dllexport, visibility("default"))) const size_t COMMITMENT_SIZE;

// Bulletproof size
extern __attribute__((dllexport, visibility("default"))) const size_t BULLETPROOF_SIZE;

// Scalar size
extern __attribute__((dllexport, visibility("default"))) const size_t SCALAR_SIZE;

// Bulletproof message size
extern __attribute__((dllexport, visibility("default"))) const size_t BULLETPROOF_MESSAGE_SIZE;

// Single-signer signature size
extern __attribute__((dllexport, visibility("default"))) const size_t SINGLE_SIGNER_SIGNATURE_SIZE;

// Single-signer signature message size
extern __attribute__((dllexport, visibility("default"))) const size_t SINGLE_SIGNER_SIGNATURE_MESSAGE_SIZE;


// Function prototypes

// Initializing succeeded
extern "C" __attribute__((dllexport, visibility("default"))) bool initializingSucceeded();

// Is valid private key
extern "C" __attribute__((dllexport, visibility("default"))) bool isValidPrivateKey(const char *privateKey);

// Get public key
extern "C" __attribute__((dllexport, visibility("default"))) bool getPublicKey(uint8_t *serializedPublicKey, const char *privateKey);

// Add private keys
extern "C" __attribute__((dllexport, visibility("default"))) bool addPrivateKeys(char *firstPrivateKey, const char *secondPrivateKey);

// Get blinding factor
extern "C" __attribute__((dllexport, visibility("default"))) bool getBlindingFactor(uint8_t *blindingFactor, const char *blind, const char *value);

// Get commitment
extern "C" __attribute__((dllexport, visibility("default"))) bool getCommitment(uint8_t *serializedCommitment, const char *blindingFactor, const char *value);

// Get Bulletproof
extern "C" __attribute__((dllexport, visibility("default"))) bool getBulletproof(uint8_t *bulletproof, const char *blindingFactor, const char *value, const char *rewindNonce, const char *privateNonce, const char *message);

// Get private nonce
extern "C" __attribute__((dllexport, visibility("default"))) bool getPrivateNonce(uint8_t *privateNonce);

// Combine public keys
extern "C" __attribute__((dllexport, visibility("default"))) bool combinePublicKeys(uint8_t *serializedCombinedPublicKey, const char **serializedPublicKeys, const size_t numberOfSerializedPublicKeys);

// Get partial single-signer signature
extern "C" __attribute__((dllexport, visibility("default"))) bool getPartialSingleSignerSignature(uint8_t *serializedSignature, const char *privateKey, const char *message, const char *privateNonce, const char *serializedPublicKey, const char *serializedPublicNonce);

// Public key to commitment
extern "C" __attribute__((dllexport, visibility("default"))) bool publicKeyToCommitment(uint8_t * serializedCommitment, const char *serializedPublicKey);


#endif
