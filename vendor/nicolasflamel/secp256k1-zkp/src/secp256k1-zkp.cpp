// Header files
#define __STDC_WANT_LIB_EXT1__ 1
#include <cstring>
#include <limits>
#include <memory>
#include "secp256k1_aggsig.h"
#include "secp256k1_bulletproofs.h"
#include "./secp256k1-zkp.h"

// Check if Windows
#ifdef _WIN32

	// Header files
	#include <windows.h>
	#include <ntstatus.h>

// Otherwise check if macOS
#elif defined __APPLE__

	// Header files
	#import <Security/SecRandom.h>
#endif

using namespace std;


// Function prototypes

// Create context
static secp256k1_context *createContext();

// Securely clear
static void securelyClear(void *data, const size_t length);

// Random fill
static bool randomFill(uint8_t *data, const size_t length);


// Constants

// Private key size
const size_t PRIVATE_KEY_SIZE = 32;

// Public key size
const size_t PUBLIC_KEY_SIZE = 33;

// Blinding factor size
const size_t BLINDING_FACTOR_SIZE = 32;

// Commitment size
const size_t COMMITMENT_SIZE = 33;

// Bulletproof size
const size_t BULLETPROOF_SIZE = 675;

// Scalar size
const size_t SCALAR_SIZE = 32;

// Bulletproof message size
const size_t BULLETPROOF_MESSAGE_SIZE = 20;

// Single-signer signature size
const size_t SINGLE_SIGNER_SIGNATURE_SIZE = 64;

// Single-signer signature message size
const size_t SINGLE_SIGNER_SIGNATURE_MESSAGE_SIZE = 32;

// Bytes in a kilobyte
static const int BYTES_IN_A_KILOBYTE = 1024;

// Context
static const unique_ptr<secp256k1_context, decltype(&secp256k1_context_destroy)> context(createContext(), secp256k1_context_destroy);

// Scratch space size
static const size_t SCRATCH_SPACE_SIZE = 30 * BYTES_IN_A_KILOBYTE;

// Scratch space
static thread_local const unique_ptr<secp256k1_scratch_space, decltype(&secp256k1_scratch_space_destroy)> scratchSpace(secp256k1_scratch_space_create(context.get(), SCRATCH_SPACE_SIZE), secp256k1_scratch_space_destroy);

// Number of generators
static const size_t NUMBER_OF_GENERATORS = 256;

// Generators
static const unique_ptr<secp256k1_bulletproof_generators, void(*)(secp256k1_bulletproof_generators *)> generators(secp256k1_bulletproof_generators_create(context.get(), &secp256k1_generator_const_g, NUMBER_OF_GENERATORS), [](secp256k1_bulletproof_generators *generators) {

	// Free generators
	secp256k1_bulletproof_generators_destroy(context.get(), generators);
});

// Generator J public
static const secp256k1_pubkey GENERATOR_J = {{0x5F, 0x15, 0x21, 0x36, 0x93, 0x93, 0x01, 0x2A, 0x8D, 0x8B, 0x39, 0x7E, 0x9B, 0xF4, 0x54, 0x29, 0x2F, 0x5A, 0x1B, 0x3D, 0x38, 0x85, 0x16, 0xC2, 0xF3, 0x03, 0xFC, 0x95, 0x67, 0xF5, 0x60, 0xB8, 0x3A, 0xC4, 0xC5, 0xA6, 0xDC, 0xA2, 0x01, 0x59, 0xFC, 0x56, 0xCF, 0x74, 0x9A, 0xA6, 0xA5, 0x65, 0x31, 0x6A, 0xA5, 0x03, 0x74, 0x42, 0x3F, 0x42, 0x53, 0x8F, 0xAA, 0x2C, 0xD3, 0x09, 0x3F, 0xA4}};


// Supporting function implementation

// Initializing succeeded
bool initializingSucceeded() {

	// Check if creating context, scratch space, and generators was successful
	return context && scratchSpace && generators;
}

// Is valid private key
bool isValidPrivateKey(const char *privateKey) {

	// Return if private key is a valid private key
	return secp256k1_ec_seckey_verify(secp256k1_context_no_precomp, reinterpret_cast<const uint8_t *>(privateKey));
}

// Get public key
bool getPublicKey(uint8_t *serializedPublicKey, const char *privateKey) {

	// Check if getting private key's public key failed
	secp256k1_pubkey publicKey;
	if(!secp256k1_ec_pubkey_create(context.get(), &publicKey, reinterpret_cast<const uint8_t *>(privateKey))) {
	
		// Securely clear public key
		securelyClear(&publicKey, sizeof(publicKey));
		
		// Return false
		return false;
	}
	
	// Check if serializing public key failed
	size_t serializedPublicKeyLength = PUBLIC_KEY_SIZE;
	if(!secp256k1_ec_pubkey_serialize(secp256k1_context_no_precomp, serializedPublicKey, &serializedPublicKeyLength, &publicKey, SECP256K1_EC_COMPRESSED) || serializedPublicKeyLength != PUBLIC_KEY_SIZE) {
	
		// Securely clear public key
		securelyClear(&publicKey, sizeof(publicKey));
		
		// Securely clear serialized public key
		securelyClear(serializedPublicKey, PUBLIC_KEY_SIZE);
		
		// Return false
		return false;
	}
	
	// Securely clear public key
	securelyClear(&publicKey, sizeof(publicKey));
	
	// Return true
	return true;
}

// Add private keys
bool addPrivateKeys(char *firstPrivateKey, const char *secondPrivateKey) {

	// Return if adding second private key to the first private key was successful
	return secp256k1_ec_privkey_tweak_add(secp256k1_context_no_precomp, reinterpret_cast<uint8_t *>(firstPrivateKey), reinterpret_cast<const uint8_t *>(secondPrivateKey));
}

// Get blinding factor
bool getBlindingFactor(uint8_t *blindingFactor, const char *blind, const char *value) {
	
	// Check if gett value as as number failed
	const uint64_t valueAsNumber = strtoull(value, nullptr, 10);
	if(valueAsNumber == ULLONG_MAX && errno == ERANGE) {
	
		// Return false
		return false;
	}
	
	// Check if performing blind switch failed
	if(!secp256k1_blind_switch(context.get(), blindingFactor, reinterpret_cast<const uint8_t *>(blind), valueAsNumber, &secp256k1_generator_const_h, &secp256k1_generator_const_g, &GENERATOR_J)) {
	
		// Securely clear blinding factor
		securelyClear(blindingFactor, BLINDING_FACTOR_SIZE);
		
		// Return false
		return false;
	}
	
	// Check if blinding factor isn't a valid private key
	if(!isValidPrivateKey(reinterpret_cast<const char *>(blindingFactor))) {
	
		// Securely clear blinding factor
		securelyClear(blindingFactor, BLINDING_FACTOR_SIZE);
		
		// Return false
		return false;
	}
	
	// Return true
	return true;
}

// Get commitment
bool getCommitment(uint8_t *serializedCommitment, const char *blindingFactor, const char *value) {

	// Check if gett value as as number failed
	const uint64_t valueAsNumber = strtoull(value, nullptr, 10);
	if(valueAsNumber == ULLONG_MAX && errno == ERANGE) {
	
		// Return false
		return false;
	}
	
	// Check if committing to value with the blinding factor failed
	secp256k1_pedersen_commitment commitment;
	if(!secp256k1_pedersen_commit(secp256k1_context_no_precomp, &commitment, reinterpret_cast<const uint8_t *>(blindingFactor), valueAsNumber, &secp256k1_generator_const_h, &secp256k1_generator_const_g)) {
	
		// Return false
		return false;
	}
	
	// Check if serializing the commitment failed
	if(!secp256k1_pedersen_commitment_serialize(secp256k1_context_no_precomp, serializedCommitment, &commitment)) {
	
		// Securely clear serialized commitment
		securelyClear(serializedCommitment, COMMITMENT_SIZE);
		
		// Return false
		return false;
	}
	
	// Return true
	return true;
}

// Get Bulletproof
bool getBulletproof(uint8_t *bulletproof, const char *blindingFactor, const char *value, const char *rewindNonce, const char *privateNonce, const char *message) {

	// Check if gett value as as number failed
	const uint64_t valueAsNumber = strtoull(value, nullptr, 10);
	if(valueAsNumber == ULLONG_MAX && errno == ERANGE) {
	
		// Return false
		return false;
	}
	
	// Check if creating Bulletproof failed
	size_t bulletproofLength = BULLETPROOF_SIZE;
	if(!secp256k1_bulletproof_rangeproof_prove(context.get(), scratchSpace.get(), generators.get(), bulletproof, &bulletproofLength, nullptr, nullptr, nullptr, &valueAsNumber, nullptr, reinterpret_cast<const uint8_t **>(&blindingFactor), nullptr, 1, &secp256k1_generator_const_h, numeric_limits<uint64_t>::digits, reinterpret_cast<const uint8_t *>(rewindNonce), reinterpret_cast<const uint8_t *>(privateNonce), nullptr, 0, reinterpret_cast<const uint8_t *>(message)) || bulletproofLength != BULLETPROOF_SIZE) {
	
		// Securely clear Bulletproof
		securelyClear(bulletproof, BULLETPROOF_SIZE);
		
		// Return false
		return false;
	}
	
	// Return true
	return true;
}

// Get private nonce
bool getPrivateNonce(uint8_t *privateNonce) {

	// Check if creating random seed failed
	uint8_t seed[SCALAR_SIZE];
	if(!randomFill(seed, sizeof(seed))) {
	
		// Securely clear seed
		securelyClear(seed, sizeof(seed));
		
		// Return false
		return false;
	}
	
	// Check if creating private nonce failed
	if(!secp256k1_aggsig_export_secnonce_single(context.get(), privateNonce, seed)) {
	
		// Securely clear private nonce
		securelyClear(privateNonce, SCALAR_SIZE);
		
		// Securely clear seed
		securelyClear(seed, sizeof(seed));
		
		// Return false
		return false;
	}
	
	// Securely clear seed
	securelyClear(seed, sizeof(seed));
	
	// Return true
	return true;
}

// Combine public keys
bool combinePublicKeys(uint8_t *serializedCombinedPublicKey, const char **serializedPublicKeys, const size_t numberOfSerializedPublicKeys) {

	// Go through all serialized publc keys
	secp256k1_pubkey publicKeys[numberOfSerializedPublicKeys];
	const secp256k1_pubkey *publicKeysAddresses[numberOfSerializedPublicKeys];
	for(size_t i = 0; i < numberOfSerializedPublicKeys; ++i) {
	
		// Check if parsing serialized public key failed
		if(!secp256k1_ec_pubkey_parse(secp256k1_context_no_precomp, &publicKeys[i], reinterpret_cast<const uint8_t *>(serializedPublicKeys[i]), PUBLIC_KEY_SIZE)) {
		
			// Return false
			return false;
		}
		
		// Get public key's address
		publicKeysAddresses[i] = &publicKeys[i];
	}

	// Check if combining public keys failed
	secp256k1_pubkey combinedPublicKey;
	if(!secp256k1_ec_pubkey_combine(secp256k1_context_no_precomp, &combinedPublicKey, publicKeysAddresses, numberOfSerializedPublicKeys)) {
		
		// Return false
		return false;
	}
	
	// Check if serializing combined public key failed
	size_t serializedCombinedPublicKeyLength = PUBLIC_KEY_SIZE;
	if(!secp256k1_ec_pubkey_serialize(secp256k1_context_no_precomp, serializedCombinedPublicKey, &serializedCombinedPublicKeyLength, &combinedPublicKey, SECP256K1_EC_COMPRESSED) || serializedCombinedPublicKeyLength != PUBLIC_KEY_SIZE) {
	
		// Securely clear serialized combined public key
		securelyClear(serializedCombinedPublicKey, PUBLIC_KEY_SIZE);
		
		// Return false
		return false;
	}
	
	// Return true
	return true;
}

// Get partial single-signer signature
bool getPartialSingleSignerSignature(uint8_t *serializedSignature, const char *privateKey, const char *message, const char *privateNonce, const char *serializedPublicKey, const char *serializedPublicNonce) {

	// Check if parsing public key failed
	secp256k1_pubkey publicKey;
	if(!secp256k1_ec_pubkey_parse(secp256k1_context_no_precomp, &publicKey, reinterpret_cast<const uint8_t *>(serializedPublicKey), PUBLIC_KEY_SIZE)) {
	
		// Return false
		return false;
	}
	
	// Check if parsing public nonce failed
	secp256k1_pubkey publicNonce;
	if(!secp256k1_ec_pubkey_parse(secp256k1_context_no_precomp, &publicNonce, reinterpret_cast<const uint8_t *>(serializedPublicNonce), PUBLIC_KEY_SIZE)) {
	
		// Return false
		return false;
	}
	
	// Check if creating random seed failed
	uint8_t seed[SCALAR_SIZE];
	if(!randomFill(seed, sizeof(seed))) {
	
		// Securely clear seed
		securelyClear(seed, sizeof(seed));
		
		// Return false
		return false;
	}
	
	// Check if signing message failed
	secp256k1_ecdsa_signature signature;
	if(!secp256k1_aggsig_sign_single(context.get(), signature.data, reinterpret_cast<const uint8_t *>(message), reinterpret_cast<const uint8_t *>(privateKey), reinterpret_cast<const uint8_t *>(privateNonce), nullptr, &publicNonce, &publicNonce, &publicKey, seed)) {
	
		// Securely clear seed
		securelyClear(seed, sizeof(seed));
		
		// Return false
		return false;
	}
	
	// Securely clear seed
	securelyClear(seed, sizeof(seed));
	
	// Check if getting private key's public key failed
	secp256k1_pubkey privateKeysPublicKey;
	if(!secp256k1_ec_pubkey_create(context.get(), &privateKeysPublicKey, reinterpret_cast<const uint8_t *>(privateKey))) {
	
		// Return false
		return false;
	}
	
	// Check if verifying signature failed
	if(!secp256k1_aggsig_verify_single(context.get(), signature.data, reinterpret_cast<const uint8_t *>(message), &publicNonce, &privateKeysPublicKey, &publicKey, nullptr, true)) {
	
		// Return false
		return false;
	}
	
	// Check if serializing signature failed
	if(!secp256k1_ecdsa_signature_serialize_compact(secp256k1_context_no_precomp, serializedSignature, &signature)) {
	
		// Securely clear serialized signature
		securelyClear(serializedSignature, SINGLE_SIGNER_SIGNATURE_SIZE);
		
		// Return false
		return false;
	}
	
	// Return true
	return true;
}

// Public key to commitment
bool publicKeyToCommitment(uint8_t * serializedCommitment, const char *serializedPublicKey) {

	// Check if parsing serialized public key failed
	secp256k1_pubkey publicKey;
	if(!secp256k1_ec_pubkey_parse(secp256k1_context_no_precomp, &publicKey, reinterpret_cast<const uint8_t *>(serializedPublicKey), PUBLIC_KEY_SIZE)) {
	
		// Return false
		return false;
	}
	
	// Check if getting commitment from public key failed
	secp256k1_pedersen_commitment commitment;
	if(!secp256k1_pubkey_to_pedersen_commitment(secp256k1_context_no_precomp, &commitment, &publicKey)) {
	
		// Return false
		return false;
	}
	
	// Check if serializing commitment failed
	if(!secp256k1_pedersen_commitment_serialize(secp256k1_context_no_precomp, serializedCommitment, &commitment)) {
	
		// Securely clear serialized commitment
		securelyClear(serializedCommitment, COMMITMENT_SIZE);
		
		// Return false
		return false;
	}
	
	// Return true
	return true;
}

// Create context
secp256k1_context *createContext() {

	// Check if creating context failed
	secp256k1_context *context = secp256k1_context_create(SECP256K1_CONTEXT_SIGN | SECP256K1_CONTEXT_VERIFY);
	if(!context) {
	
		// Return nothing
		return nullptr;
	}
	
	// Check if creating random seed failed
	uint8_t seed[SCALAR_SIZE];
	if(!randomFill(seed, sizeof(seed))) {
	
		// Securely clear seed
		securelyClear(seed, sizeof(seed));
		
		// Destroy context
		secp256k1_context_destroy(context);
		
		// Return nothing
		return nullptr;
	}
	
	// Check if randomizing context failed
	if(!secp256k1_context_randomize(context, seed)) {
	
		// Securely clear seed
		securelyClear(seed, sizeof(seed));
		
		// Destroy context
		secp256k1_context_destroy(context);
		
		// Return nothing
		return nullptr;
	}
	
	// Securely clear seed
	securelyClear(seed, sizeof(seed));
	
	// Return context
	return context;
}

// Securely clear
void securelyClear(void *data, const size_t length) {

	// Check if Windows
	#ifdef _WIN32
	
		// Securely clear data
		SecureZeroMemory(data, length);
	
	// Otherwise check if macOS
	#elif defined __APPLE__
	
		// Securely clear data
		memset_s(data, length, 0, length);
		
	// Otherwise
	#else
	
		// Securely clear data
		explicit_bzero(data, length);
	#endif
}

// Random fill
bool randomFill(uint8_t *data, const size_t length) {

	// Check if Windows
	#ifdef _WIN32
	
		// Return if filling the data randomly was successful
		return BCryptGenRandom(nullptr, data, length, BCRYPT_USE_SYSTEM_PREFERRED_RNG) == STATUS_SUCCESS;
	
	// Otherwise check if macOS
	#elif defined __APPLE__
	
		// Return if filling the data randomly was successful
		return SecRandomCopyBytes(kSecRandomDefault, length, data) == errSecSuccess;
		
	// Otherwise
	#else
	
		// Return if filling the data randomly was successful
		return !getentropy(data, length);
	#endif
}
