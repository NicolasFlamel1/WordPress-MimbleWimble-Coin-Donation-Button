// Header files
#include "blake2.h"
#include "./blake2b.h"

using namespace std;


// Supporting function implementation

// Compute
bool compute(uint8_t *result, const size_t resultSize, const char *input, const size_t inputSize, const char *key, const size_t keySize) {

	// Return if computing BLAKE2b was successful
	return !blake2b(result, resultSize, input, inputSize, key, keySize);
}
